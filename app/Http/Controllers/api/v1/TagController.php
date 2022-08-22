<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Models\Tag;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AccessController;

class TagController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'store_id' => 'required|int',
        ]);

        try {

            if (AccessController::access_staff($request['store_id'])) {
                $response = Tag::create([
                    'name' => $request['name'],
                    'store_id' => $request['store_id'],
                ]);
                return $this->commonResponse(true, 'Tag Created successfully', $response, Response::HTTP_CREATED);
            }

            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_OK); //แก้
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

            $tag = tag::all();

            return $this->commonResponse(true, 'show successfully', $tag, Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {

        try {
            if (AccessController::access_staff($request['store_id'])) {

                $tag = Tag::find($id);
                $store = Store::find($id);

                $tag->update([
                    'name' => $request['name'],
                    'store' => $request['store_id'],

                ]);

                $user = User::find($store['users_id']);
                $store = Store::find($tag['store_id']);

                $response = [
                    'name' => $tag['name'],
                    'user' => $user['name'],
                    'store_id' => $tag['store_id'],
                ];
                return $this->commonResponse(true, 'update successfully', $response, Response::HTTP_OK);
            }

            return $this->commonResponse(true, 'ไม่มีสิทธิ', '', Response::HTTP_OK); //แก้
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function destroy(int $id)
    {
        // $tag = Tag::find($id);
        // $store = Store::find($id);

        // if (Auth::user()->id != $tag['store_id'] && Auth::user()->id != $store['users_id']) {
        //     return $this->commonResponse(true, 'you are not staff in this store', '', Response::HTTP_NOT_FOUND);
        // }
        // $tag = tag::find($id);
        // $tag->delete();
        // return $tag;
    }
}
