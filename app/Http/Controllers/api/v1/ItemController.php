<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Item;
use App\Models\User;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccessController;

class ItemController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        //if (Auth::user()->owner == 1 && Auth::user()->verify == 1 ){}
        $request->validate([
            'name' => 'required|string',
            'description' => 'string',
            'store_id' => 'required|integer',
            'is_active' => 'required|integer',
            'is_not_return' => 'required|integer',
        ]);

        try {
            if (AccessController::access_staff($request['store_id'])) {
                $item = Item::create([
                    'name' => $request['name'],
                    'description' => $request['description'],
                    'store_id' => $request['store_id'],
                    'amount' => 0,
                    'is_active' => $request['is_active'],
                    'is_not_return' => $request['is_not_return'],
                    'updated_by' => Auth::user()->id,
                    'amount_update_at' => Carbon::now()->setTimezone('Asia/Bangkok')->toDateTimeString(),
                ]);

                $response = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'store_id' => $item['store_id'],
                    'amount' => 0,
                    'is_active' => $item['is_active'],
                    'is_not_return' => $item['is_not_return'],
                    'updated_by' => $item['updated_by'],
                    'updated_by_name' => User::find($item['updated_by'])->name,
                ];

                return $this->commonResponse(true, 'Create successfully', $response, Response::HTTP_CREATED);
            }
            return $this->commonResponse(true, '??????????????????????????????', '', Response::HTTP_FORBIDDEN); //?????????
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
            if (AccessController::access_staff($id) || AccessController::access_member($id)) {
                $store = Item::where('store_id', '=', $id)->get();
                return $this->commonResponse(true, 'show successfully', $store, Response::HTTP_OK);
            }
            return $this->commonResponse(true, '??????????????????????????????', '', Response::HTTP_FORBIDDEN); //?????????

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
            'name' => 'string',
            'description' => 'string',
            'is_active' => 'integer',
            'is_not_return' => 'integer',
        ]);

        $item = Item::find($id);

        try {
            $store = Store::find($item['store_id']);

            if (AccessController::access_owner($store['id']) || AccessController::access_staff($store['id'])) {

                $item->update([
                    'name' => $request['name'],
                    'description' => $request['description'],
                    'is_active' => $request['is_active'],
                    'is_not_return' => $request['is_not_return']
                ]);
                return $this->commonResponse(true, 'update successfully', $item, Response::HTTP_OK);
            }
            return $this->commonResponse(true, '??????????????????????????????', '', Response::HTTP_FORBIDDEN); //?????????

        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
