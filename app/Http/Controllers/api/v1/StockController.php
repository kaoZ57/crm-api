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

class StockController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|int',
            'amount' => 'required|int ',
            //'updated_by' => 'int',
        ]);

        try {

            $store = Store::find($request['user_id']);

            $stocker = DB::table('stock')
                ->join('item', 'stock.item_id', '=', 'item_id')
                ->select('item.store_id', 'item.name', 'item.description', 'item.is_active', 'item.updated_by', 'item.amount',)
                ->get();

            $stock = Stock::create([
                'item_id' => $request['item_id'],
                'amount' => $request['amount'],

            ]);

            $response = [
                'item_id' => $stock['item_id'],
                'amount' => $stock['amount'],
                'Item' =>  $stocker,

            ];

            return $this->commonResponse(true, 'Stock Created Successfully', $response, Response::HTTP_CREATED);

            //sreturn $this->commonResponse(false, 'you are not staff in this store', '', Response::HTTP_NOT_FOUND);
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
