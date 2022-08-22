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

class Store_Member_Controller extends Controller
{
    public function store(int $id): JsonResponse
    {

        try {

            $approve = 0;

            $statusArr = Status::where('table_name', 'like', 'store_members')->get();

            $memberArr = Store_members::where('store_id', '=', $id)->get();

            //check
            foreach ($memberArr as $value) {
                if ($value['users_id'] == Auth::user()->id) {
                    return $this->commonResponse(true, "you're ever followed this store", '', Response::HTTP_OK);
                }
            }

            $store = Store::find($id);
            if (Auth::user()->id == $store['users_id']) {
                return $this->commonResponse(true, 'can not follow you are owner in this store', '', Response::HTTP_NOT_FOUND);
            }

            if ($store['follow_approve'] == 0) {
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
                'store' => Store::find($id)->name,
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
        $store = Store::find($id);

        try {
            $store = Store::find($id);

            if (Auth::user()->id != $store['users_id']) {
                return $this->commonResponse(true, 'you are not owner in this store', '', Response::HTTP_NOT_FOUND);
            }

            // $membersArr = Store_members::where('store_id', '=', $id)->get();

            $membersArr = DB::table('store_members')
                ->join('users', 'store_members.users_id', '=', 'users.id')
                ->join('store', 'store_members.store_id', '=', 'store.id')
                ->join('status', 'store_members.status_id', '=', 'status.id')
                ->select('store_members.users_id', 'store_members.store_id', 'store_members.status_id', 'users.name as users_name', 'store.name as store_name', 'status.name as status_name', 'store_members.is_active')
                ->get();

            $response = [
                'members' => $membersArr,
            ];

            return $this->commonResponse(true, 'show successfully', $response, Response::HTTP_OK);
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

            $members = Store_members::where('users_id', $id);
            $membersData = Store_members::find($members['id']);
            $members->update(array('status_id' => 10));

            $response = [
                'members' => $membersData,
            ];

            return $this->commonResponse(true, 'show successfully', $response, Response::HTTP_OK);
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
