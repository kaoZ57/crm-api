<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AccessController;
use App\Models\Out_of_service;
use App\Models\Item;
use App\Models\Store;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class OutOfServiceController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'note' => 'string',
            'amount' => 'required|integer',
            'ready_to_use' => 'required|integer',
            'store_id' => 'required|integer',
        ]);


        try {
            if (AccessController::access_staff($request['store_id'])) {
                $response = Out_of_service::create([
                    'item_id' => $request['item_id'],
                    'note' => $request['note'],
                    'amount' => $request['amount'],
                    'ready_to_use' => $request['ready_to_use'],
                    'updated_by' => Auth::user()->id,
                    'store_id' => $request['store_id'],

                ]);

                return $this->commonResponse(true, 'Create successfully', $response, Response::HTTP_CREATED);
            }
            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show(int $id): JsonResponse
    {
        $store = DB::select("select * from item where store_id = $id");

        $itemArr = array();
        foreach ($store as $value) {
            $item = DB::select("select * from out_of_service where item_id =  $value->id");
            $itemArr = array_push($itemArr, $item);
        }


        return $this->commonResponse(true, 'show successfully', $itemArr, Response::HTTP_OK);
        //$item = DB::select('select item_id from out_of_service');




        try {

            if (AccessController::access_staff($id)) {

                $store = Out_of_service::all();

                return $this->commonResponse(true, 'show successfully', $store, Response::HTTP_OK);
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

    // public function update(Request $request, int $id): JsonResponse
    // {
    //     $request->validate([
    //         'name' => 'string',
    //         'description' => 'string',
    //         'is_active' => 'integer',
    //         'is_not_return' => 'integer',
    //     ]);

    //     $item = Item::find($id);

    //     try {
    //         $store = Store::find($item['store_id']);

    //         if (AccessController::access_owner($request['store_id']) || AccessController::access_staff($request['store_id'])) {

    //             $item->update([
    //                 'name' => $request['name'],
    //                 'description' => $request['description'],
    //                 'is_active' => $request['is_active'],
    //                 'is_not_return' => $request['is_not_return']
    //             ]);
    //             return $this->commonResponse(true, 'update successfully', $item, Response::HTTP_OK);
    //         }
    //         return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้

    //     } catch (QueryException $exception) {
    //         return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
    //     } catch (Exception $exception) {
    //         Log::critical(': ' . $exception->getTraceAsString());
    //         return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }
