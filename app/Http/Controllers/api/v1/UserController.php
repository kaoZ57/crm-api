<?php

namespace App\Http\Controllers\api\v1;

use App\Events\UserCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Psy\Util\Json;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

/**
 * User Management
 *
 * @group Users
 * Class UserController
 * @package App\Http\Controllers\api\v1
 */
class UserController extends Controller
{
    /**
     * List All Users
     *
     * @return JsonResponse
     * @authenticated
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::with('customers', 'roles')->latest()->paginate(10);
            if ($users->isEmpty()) {
                return $this->commonResponse(false, 'Users Not Found', '', Response::HTTP_NOT_FOUND);
            }
            return $this->commonResponse(true, 'Users List', UserResource::collection($users)->response()->getData(true), Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Failed to fetch user data. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create New User
     *
     * @param UserRequest $request
     * @bodyParam name string required The User Name
     * @bodyParam email email required User Email
     * @bodyParam password password required User Password
     * @bodyParam password_confirmation password required Password Confirmation
     * @return JsonResponse
     * @authenticated
     */
    public function store(UserRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $request->rules(), $request->messages());
        if ($validator->fails()) {
            return $this->commonResponse(false, Arr::flatten($validator->messages()->get('*')), '', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $newUser = User::create(array_merge(
                $request->validated(),
                ['password' => Hash::make($request->password)]
            ));
            if ($newUser) {
                //TODO send the new user an invitation to set their reset their password
                UserCreated::dispatch($newUser); //assign the user a user role
                return $this->commonResponse(true, 'User Created successfully', new UserResource($newUser), Response::HTTP_CREATED);
            }
            return $this->commonResponse(false, 'Failed to create user', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not create new user account. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display User Details
     *
     * @param int $id
     * @urlParam id integer required User ID
     * @return JsonResponse
     * @authenticated
     */
    public function show(): JsonResponse
    {
        try {
            // $user = User::with('customers','roles')->find($id);
            // if(!$user){
            //     return $this->commonResponse(false,'User Not Found','',Response::HTTP_NOT_FOUND);
            // }
            // return $this->commonResponse(true,'User Details',new UserResource($user),Response::HTTP_OK);
            $user = User::with('customers', 'roles')->find(Auth::user()->id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }

            $response = [
                'id' => $user['id'],
                'user' => $user['name'],
                'email' => $user['email'],
                'roles' => $user['roles']
            ];
            return $this->commonResponse(true, 'User Details', $response, Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not fetch user details. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update User Details
     *
     * @param UserUpdateRequest $request
     * @param int $id
     * @bodyParam name string required The name of the user
     * @bodyParam email string required The email of the user
     * @urlParam id integer required The User ID.
     * @return JsonResponse
     * @authenticated
     */
    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return $this->commonResponse(false, Arr::flatten($validator->messages()->get('*')), '', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            if ($user->update($request->validated())) {
                return $this->commonResponse(true, 'User Details Updated Successfully', new UserResource($user), Response::HTTP_OK);
            }
            return $this->commonResponse(false, 'Failed to update user details', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not update user details. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Delete User
     *
     * @param int $id
     * @urlParam id integer required The ID of the User
     * @return JsonResponse
     * @authenticated
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            if ($user->delete()) {
                return $this->commonResponse(true, 'User Deleted Successfully', '', Response::HTTP_OK);
            }
            return $this->commonResponse(false, 'Failed to delete user', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not delete user. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Change Admin Status
     * @param int $id
     * @urlParam id integer required The User ID
     * @return JsonResponse
     * @authenticated
     */
    public function makeAdmin(int $id): JsonResponse
    {
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            $adminRole = Role::findOrCreate('admin', 'api');
            $userRole  = Role::findOrCreate('user', 'api');
            if ($user->hasRole($adminRole)) {
                return $this->commonResponse(false, 'This user has an admin status already', '', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($user->assignRole($adminRole)) {
                return $this->commonResponse(true, 'User admin status changed successfully', '', Response::HTTP_OK);
            }
            return $this->commonResponse(false, 'Failed to change admin status', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not change user to admin status. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * List User Specific Roles
     * @param int $id
     * @return JsonResponse
     * @urlParam id integer The User ID
     * @authenticated
     */
    public function roles(int $id): JsonResponse
    {
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            $UserRoles = $user->roles()->latest()->paginate(10);
            return $this->commonResponse(true, 'Assigned Roles', RoleResource::collection($UserRoles)->response()->getData(true), Response::HTTP_OK);;
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not fetch user roles. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assign Role|Multiple Roles
     * @param Request $request
     * @param int $id
     * @bodyParam role_id required The Role ID
     * @urlParam id integer required the User ID to be assigned roles
     * @return JsonResponse
     * @authenticated
     */
    public function assignRoles(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), ['role_id.*' => 'required|integer']); //exists:roles,id
        if ($validator->fails()) {
            return $this->commonResponse(false, Arr::flatten($validator->messages()->get('*')), '', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            //check for role by id
            $role = Role::findById((int)$request->role_id, 'api');
            if (!$role) {
                return $this->commonResponse(false, 'Role Not Found', '', Response::HTTP_NOT_FOUND);
            }
            $roleIds = explode(',', $request->role_id);
            if (count($roleIds) > 1) {
                return $this->assignMultipleRoles($request, $user);
            }
            if ($user->hasRole($role->name)) {
                return $this->commonResponse(false, 'User Has ' . $role->name . ' role already', '', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $user->assignRole($role);
            $assignedRoles = $user->roles()->latest()->paginate(10);
            return $this->commonResponse(false, 'User Assigned ' . $role->name . ' successfully', RoleResource::collection($assignedRoles), Response::HTTP_OK);;
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Failed to assign roles. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Revoke Role|Roles from a user
     * @param Request $request
     * @param int $id
     * @urlParam id integer required User ID
     * @bodyParam role_id required The role(s) to be revoked(for many roles, use comma separated IDs)
     * @return JsonResponse
     * @authenticated
     */
    public function revokeRoles(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), ['role_id.*' => 'required|integer|exists:roles,id']);
        if ($validator->fails()) {
            return $this->commonResponse(false, Arr::flatten($validator->messages()->get('*')), '', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        try {
            $user = User::with('customers', 'roles')->find($id);
            if (!$user) {
                return $this->commonResponse(false, 'User Not Found', '', Response::HTTP_NOT_FOUND);
            }
            $roleIds = explode(',', $request->role_id);
            if (count($roleIds) > 1) {
                return $this->revokeMultipleRoles($request, $user);
            }
            $role = Role::findById((int)$request->role_id, 'api');
            if (!$role) {
                return $this->commonResponse(false, 'Role Not Found', '', Response::HTTP_NOT_FOUND);
            }
            if (!$user->hasRole($role)) {
                return $this->commonResponse(false, 'User has no ' . $role->name . ' role', '', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($user->removeRole($role)) {
                return $this->commonResponse(true, 'Role revoked successfully', '', Response::HTTP_OK);
            }
            return $this->commonResponse(false, 'Could Not Revoke Role, please try again', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Could not revoke user role. ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Request $request
     * @param $user
     * @return JsonResponse
     */
    private function assignMultipleRoles(Request $request, $user): JsonResponse
    {
        $roleIds = explode(',', $request->role_id);
        $userRoles = Role::whereIn('id', $roleIds)->get();
        if ($user->hasAnyRole($userRoles)) {
            foreach ($userRoles as $role) {
                return $this->commonResponse(false, $role->name . ' already assigned to user', '', Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }
        $user->assignRole($userRoles);
        $roles = $user->roles()->latest()->paginate(10);
        return $this->commonResponse(true, 'Roles Assigned Successfully to user', RoleResource::collection($roles)->response()->getData(true), Response::HTTP_OK);
    }

    /**
     * Remove Multiple User Roles
     * @param Request $request
     * @param $user
     * @return JsonResponse
     */
    private function revokeMultipleRoles(Request $request, $user): JsonResponse
    {
        $roleIds = explode(',', $request->role_id);
        try {
            $roles = Role::whereIn('id', $roleIds)->get();
            foreach ($roles as $role) {
                if (!$user->hasRole($role)) {
                    return $this->commonResponse(false, 'User has no ' . $role->name . ' role', '', Response::HTTP_UNPROCESSABLE_ENTITY);
                }
                if ($user->removeRole($role)) {
                    $userRoles = $user->roles()->latest()->paginate(10);
                    return $this->commonResponse(true, 'Roles revoked successfully', RoleResource::collection($userRoles)->response()->getData(true), Response::HTTP_OK);
                }
            }
            return $this->commonResponse(false, 'Failed To Revoke Roles', '', Response::HTTP_EXPECTATION_FAILED);
        } catch (QueryException $queryException) {
            return $this->commonResponse(false, $queryException->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical('Failed to revoke multiple roles: ERROR: ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
