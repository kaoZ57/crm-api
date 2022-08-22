<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\v1\CustomerController;
use App\Http\Controllers\api\v1\UserController;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\ItemController;
use App\Http\Controllers\api\v1\ProfileController;
use App\Http\Controllers\api\v1\RoleController;
use App\Http\Controllers\api\v1\PermissionController;
use App\Http\Controllers\api\v1\StoreController;
use App\Http\Controllers\api\v1\Store_Member_Controller;
use App\Http\Controllers\api\v1\TagController;
use App\Http\Controllers\api\v1\StockController;
use App\Http\Controllers\api\v1\Tag_Item_Controller;
use App\Http\Controllers\api\v1\Out_of_Service_Controller;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    //user authentication
    Route::group(['prefix' => 'auth'], function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {
        //user management (Only admin can perform user management actions)
        Route::group(['prefix' => 'users', 'middleware' => ['role:admin']], function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('/{id}/details', [UserController::class, 'show']);
            // Route::post('/create', [UserController::class, 'store']);
            // Route::patch('/{id}/update', [UserController::class, 'update']);
            // Route::delete('/{id}/delete', [UserController::class, 'destroy']);
            // Route::get('/{id}/roles', [UserController::class, 'roles']);
            Route::post('/{id}/assign-roles', [UserController::class, 'assignRoles']);
            Route::post('/{id}/revoke-roles', [UserController::class, 'revokeRoles']);
            //Admin status(make a user an admin if not already
            // Route::group(['prefix' => 'status'], function () {
            //     Route::post('/{id}/admin', [UserController::class, 'makeAdmin']);
            // });
        });
        //user profile management
        // Route::group(['prefix' => 'user/profile', 'middleware' => ['role:user|admin']], function () {
        //     Route::get('/', [ProfileController::class, 'profile']);
        //     Route::patch('/update', [ProfileController::class, 'update']);
        // });
        //Roles and Permissions(Only admin can manage roles and permissions)
        Route::group(['middleware' => ['role:admin']], function () {
            Route::resource('roles', RoleController::class);
            // Route::resource('permissions', PermissionController::class);
        });

        Route::group(['prefix' => 'store', 'middleware' => ['role:user']], function () {
            Route::post('/create', [StoreController::class, 'store']);
            Route::get('/', [StoreController::class, 'show']);
            Route::patch('{id}/update', [StoreController::class, 'update']);
            Route::post('/users/{id}/assign-staff', [StoreController::class, 'assign']);
        });
        Route::group(['prefix' => 'tag', 'middleware' => ['role:user']], function () {
            Route::post('/create', [TagController::class, 'store']);
            Route::get('/', [TagController::class, 'show']);
            Route::patch('/{id}/update', [TagController::class, 'update']);
            Route::delete('/{id}/delete', [TagController::class, 'destroy']);
            Route::post('tag_item/{id}/create', [Tag_Item_Controller::class, 'store']);
            Route::get('tag_item/{id}', [Tag_Item_Controller::class, 'show']);
        });
        Route::group(['prefix' => 'item', 'middleware' => ['role:user']], function () {
            Route::post('/create', [ItemController::class, 'store']);
            Route::get('/', [ItemController::class, 'show']);
            Route::patch('{id}/update', [ItemController::class, 'update']);
        });

        Route::group(['prefix' => 'store_members', 'middleware' => ['role:user']], function () {
            Route::post('/{id}/follow', [Store_Member_Controller::class, 'store']);
            Route::get('/{id}/store', [Store_Member_Controller::class, 'show']);
            Route::patch('/{id}/approve', [Store_Member_Controller::class, 'update']);
        });

        Route::group(['prefix' => 'stock', 'middleware' => ['role:user']], function () {
            Route::post('/create', [StockController::class, 'store']);
            Route::get('/', [StockController::class, 'show']);
        });

        Route::group(['prefix' => 'out_of_service', 'middleware' => ['role:user']], function () {
            Route::post('/create', [Out_of_Service_Controller::class, 'store']);
            Route::get('/', [Out_of_Service_Controller::class, 'show']);
        });
    });
});
