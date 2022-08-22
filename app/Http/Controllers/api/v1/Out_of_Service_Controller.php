<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\AccessController;
use App\Models\Out_of_service;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;

class Out_of_Service_Controller extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'note' => 'string',
            'amount' => 'required|integer',
            'ready_to_use' => 'required|integer',
            'updated_by' => 'required|integer',
            'store_id' => 'required|integer',
        ]);


        try {
            if (AccessController::access_staff($request['store_id'])) {
                $response = Out_of_service::create([
                    'item_id' => $request['item_id'],
                    'note' => $request['note'],
                    'amount' => $request['amount'],
                    'ready_to_use' => $request['ready_to_use'],
                    'updated_by' => $request['updated_by'],
                    'store_id' => $request['store_id'],

                ]);

                return $this->commonResponse(true, 'Create successfully', $response, Response::HTTP_CREATED);
            }
            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_OK); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
