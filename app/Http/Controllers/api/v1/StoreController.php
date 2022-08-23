<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\AccessController;

class StoreController extends Controller
{

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'is_active' => 'required|integer',
            'follow_approve' => 'required|integer',
        ]);

        try {

            $store = Store::create([
                'name' => $request['name'],
                'users_id' => Auth::user()->id,
                'is_active' => $request['is_active'],
                'follow_approve' => $request['follow_approve']
            ]);

            //create Role
            $nameOwner = $store->id . ' owner';
            $newRoleOwner = Role::create(['name' => $nameOwner, 'guard_name' => 'api', 'store_id' => $store->id]);
            $nameStaff = $store->id . ' staff';
            Role::create(['name' => $nameStaff, 'guard_name' => 'api', 'store_id' => $store->id]);

            //add Role
            $user = User::with('customers', 'roles')->find(Auth::user()->id);;
            $user->assignRole(Role::findById($newRoleOwner->id, 'api'));

            $response = [
                'id' => $store['id'],
                'name' => $store['name'],
                'users_id' => $store['users_id'],
                'users_name' => $user->name,
                'is_active' => $store['is_active'],
                'follow_approve' => $store['follow_approve']
            ];
            return $this->commonResponse(true, 'Store Created successfully', $response, Response::HTTP_CREATED);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(): JsonResponse
    {
        try {

            $store = Store::all();

            return $this->commonResponse(true, 'show successfully', $store, Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'is_active' => 'required|integer',
            'follow_approve' => 'required|integer',
        ]);

        try {

            if (AccessController::access_owner($id)) {
                $store = Store::find($id);
                $store->update([
                    'name' => $request['name'],
                    'is_active' => $request['is_active'],
                    'follow_approve' => $request['follow_approve']
                ]);

                $user = User::find($store['users_id']);
                $response = [
                    'name' => $store['name'],
                    'user' => $user['name'],
                    'is_active' => $store['is_active'],
                    'follow_approve' => $store['follow_approve']
                ];

                return $this->commonResponse(true, 'you can uppdate this store', $response, Response::HTTP_OK);
            }
            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // public function destroy($id): JsonResponse
    // {

    // }

    public function assign(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'store_id' => 'required|integer',
        ]);

        try {
            if (AccessController::access_owner($request['store_id'])) {
                $store = Store::find($request['store_id']);

                if (Auth::user()->id != $store['users_id']) {
                    return $this->commonResponse(true, 'you are not owner in this store', '', Response::HTTP_NOT_FOUND);
                }

                $roleArr = Role::where('store_id', '=', $request['store_id'])->get();
                $user = User::with('customers', 'roles')->find($id);
                $user->assignRole(Role::findById($roleArr[1]->id, 'api'));

                $response = [
                    'role' => $user,
                ];

                return $this->commonResponse(true, 'update successfully', $response, Response::HTTP_OK);
            }
            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
