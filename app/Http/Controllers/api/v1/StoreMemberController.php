<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Store_members;
use App\Models\Status;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AccessController;

class StoreMemberController extends Controller
{
    public function store(int $id): JsonResponse
    {

        try {

            $statusArr = Status::where('table_name', 'like', 'store_members')->get();
            $memberArr = Store_members::where('store_id', '=', $id)->get();

            //check
            foreach ($memberArr as $value) {
                if ($value['users_id'] == Auth::user()->id) {
                    return $this->commonResponse(true, "you're ever followed this store", '', Response::HTTP_OK);
                }
            }

            if (AccessController::access_owner($id) || AccessController::access_staff($id)) {
                return $this->commonResponse(true, 'can not follow you are owner in this store', '', Response::HTTP_NOT_FOUND);
            }

            $store = Store::find($id);
            $approve = 0;
            if ($store['follow_approve'] == 1) {
                $approve = 0;
            } elseif ($store['follow_approve'] == 0) {
                $approve = 1;
            }

            $store_members = Store_members::create([
                'users_id' => Auth::user()->id,
                'store_id' => $id,
                'status_id' => $statusArr[$approve]->id,
                'is_active' => 1,
                'updated_by' => 0,
                'update_date' => date('Y-m-d')
            ]);

            $response = [
                'id' => $store_members['id'],
                'users' => Auth::user()->name,
                'store_id' => $store_members['store_id'],
                'store_name' => Store::find($store_members['store_id'])->name,
                'status' => Status::find($store_members['status_id'])->name,
                'is_active' => $store_members['is_active'],
                'update_date' => $store_members['update_date']
            ];

            return $this->commonResponse(true, 'Follow successfully', $response, Response::HTTP_CREATED);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {

        try {
            if (AccessController::access_staff($id)) {
                // $membersArr = Store_members::where('store_id', '=', $id)->get();

                $membersArr = DB::table('store_members')
                    ->join('users', 'store_members.users_id', '=', 'users.id')
                    ->join('store', 'store_members.store_id', '=', 'store.id')
                    ->join('status', 'store_members.status_id', '=', 'status.id')
                    ->where('store_id', '=', $id)
                    ->select('store_members.id', 'store_members.users_id', 'store_members.store_id', 'store_members.status_id', 'users.name as users_name', 'store.name as store_name', 'status.name as status_name', 'store_members.is_active')
                    ->get();

                $response = [
                    'members' => $membersArr,
                ];

                return $this->commonResponse(true, 'show successfully', $response, Response::HTTP_OK);
            }

            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(int $id)
    {
        try {
            $store_members = Store_members::find($id);
            if (!$store_members) {
                return $this->commonResponse(true, 'show successfully', 'ไม่มีข้อมูล', Response::HTTP_OK);
            }
            if ($store_members['status_id'] == 10) {
                return $this->commonResponse(true, 'show successfully', 'คนนี้อนุมัตแล้ว', Response::HTTP_OK);
            }
            if (AccessController::access_staff($store_members['store_id'])) {
                $members = Store_members::find($id);
                $members->update(array('status_id' => 10));

                $response = [
                    'id' => $members['id'],
                    'users_id' => $members['users_id'],
                    'store_id' => $members['store_id'],
                    'status_id' => $members['status_id'],
                    'is_active' => $members['is_active'],
                    'updated_by' => Auth::user()->id,
                    'update_date' => $members['update_date'],
                    'users_name' => User::find($members['users_id'])->name,
                    'store_name' => Store::find($members['store_id'])->name,
                    'status_name' => Status::find($members['status_id'])->name,
                ];

                return $this->commonResponse(true, 'show successfully', $response, Response::HTTP_OK);
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
}
