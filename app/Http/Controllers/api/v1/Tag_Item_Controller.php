<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Database\QueryException;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\Tag_Item;
use App\Models\Item;
use App\Models\Tag;
use App\Http\Controllers\AccessController;


class Tag_Item_Controller extends Controller
{
    public function store(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'tag_id' => 'required|integer',
        ]);

        try {

            $item = Item::find($request['item_id']);
            $tag = Tag::find($request['tag_id']);

            if (AccessController::access_staff($id)) {

                if (!$item || !$tag) {
                    return $this->commonResponse(true, 'ไม่มีของหรือประเภท', '', Response::HTTP_OK); //แก้
                }
                if ($item['store_id'] != $id) {
                    return $this->commonResponse(true, 'สิ่งของนี้ไม่ได้อยู่ในร้านคุณ', '', Response::HTTP_OK); //แก้
                }
                if ($tag['store_id'] != $id) {
                    return $this->commonResponse(true, 'ประเภทนี้ไม่ได้อยู่ในร้านคุณ', '', Response::HTTP_OK); //แก้
                }

                $response = Tag_Item::create([
                    'item_id' => $item['id'],
                    'tag_id' => $tag['id'],
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

    public function show(int $id): JsonResponse
    {
        try {
            $tag_item = Tag_Item::get();

            $tag = array();

            // foreach ($tag_item as $key => $value) {
            //     $response[$key]["tag_id"] = $value->tag_id;
            //     $response[$key]["item_id"] = $value->item_id;
            //     $response[$key]["tag_name"] = Tag::find($value->tag_id)->name;
            //     $response[$key]["item_name"] = Item::find($value->item_id)->name;
            // }

            foreach ($tag_item as $key => $value) {
                $tag[$key]["tag_id"] = $value->tag_id;
                $tag[$key]["tag_name"] = Tag::find($value->tag_id)->name;
                $tag[$key]["item_id"] = $value->item_id;
                $tag[$key]["item_name"] = Item::find($value->item_id)->name;
            }

            $response = ([
                'name' => $tag,
                'items' => $tag
            ]);


            return $this->commonResponse(true, 'show successfully', $response, Response::HTTP_OK);
        } catch (QueryException $exception) {
            return $this->commonResponse(false, $exception->errorInfo[2], '', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $exception) {
            Log::critical(': ' . $exception->getTraceAsString());
            return $this->commonResponse(false, $exception->getMessage(), '', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
