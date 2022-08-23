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
use Illuminate\Support\Facades\DB;

class TagItemController extends Controller
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

                $tag_item = Tag_Item::create([
                    'item_id' => $item['id'],
                    'tag_id' => $tag['id'],
                ]);

                $response = ([
                    'id' => $tag_item['id'],
                    'item_id' => $tag_item['item_id'],
                    'item_name' => $item['name'],
                    'tag_id' => $tag_item['tag_id'],
                    'tag_name' => $tag['name'],
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
        try {
            if (AccessController::access_staff($id) || AccessController::access_member($id)) {
                // $tag = Tag::where('store_id', '=', $id)->get();

                $tag_item = DB::table('tag')
                    ->join('tag_item', 'tag_item.tag_id', '=', 'tag.id')
                    ->join('item', 'item.id', '=', 'tag_item.item_id')
                    ->where('tag.store_id', '=', $id)
                    ->select('tag_item.id', 'tag.id as tag_id', 'tag.name as tag_name', 'tag.store_id', 'item.id as item_id', 'item.name as item_name')
                    ->get();

                // $tag = array();

                // foreach ($tag_item as $key => $value) {
                //     $response[$key]["tag_id"] = $value->tag_id;
                //     $response[$key]["item_id"] = $value->item_id;
                //     $response[$key]["tag_name"] = Tag::find($value->tag_id)->name;
                //     $response[$key]["item_name"] = Item::find($value->item_id)->name;
                // }

                // foreach ($tag_item as $key => $value) {
                //     $tag[$key]["tag_id"] = $value->tag_id;
                //     $tag[$key]["tag_name"] = Tag::find($value->tag_id)->name;
                //     $tag[$key]["item_id"] = $value->item_id;
                //     $tag[$key]["item_name"] = Item::find($value->item_id)->name;
                // }

                $response = $tag_item;


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
}
