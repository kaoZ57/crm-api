<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\Stock;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccessController;
use App\Models\Item;
use Carbon\Carbon;

class StockController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'amount' => 'required|numeric ',
        ]);

        try {

            $item_store = Item::find($request['item_id']);

            if (AccessController::access_staff($item_store['store_id'])) {
                $stock = Stock::create([
                    'item_id' => $request['item_id'],
                    'amount' => $request['amount'],

                ]);

                $item = Item::find($stock['item_id']);
                $item->update([
                    'amount' => $item['amount'] + $stock['amount'],
                    'amount_update_at' => Carbon::now()->setTimezone('Asia/Bangkok')->toDateTimeString(),
                ]);

                $response = [
                    'item' => $item,
                    'stock' => $stock,

                ];
                return $this->commonResponse(true, 'Stock Created Successfully', $response, Response::HTTP_CREATED);
            }
            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_FORBIDDEN); //แก้
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
            // if ($id) {
            //     $item = Store::find($id);
            //     return $this->commonResponse(true, 'show successfully', $item, Response::HTTP_OK);
            // }
            $stock = Stock::all();

            return $this->commonResponse(true, 'show successfully', $stock, Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
