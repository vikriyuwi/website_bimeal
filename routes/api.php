<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthBuyerApiController;
use App\Http\Controllers\AuthMerchantApiController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\PaymentController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
    'prefix' => 'buyer'
], function($router) {
    Route::get('/',[AuthBuyerApiController::class,'index'])->name('login');
    Route::post('login',[AuthBuyerApiController::class,'login']);
    Route::post('register',[AuthBuyerApiController::class,'register']);
    Route::group([
        // 'middleware' => 'auth:buyerAPI',
    ],function(){
        Route::get('login-data',[AuthBuyerApiController::class,'data']);
        Route::get('logout',[AuthBuyerApiController::class,'logout']);
    });
});

Route::group([
    'prefix' => 'merchant'
], function($router) {
    Route::get('/',[AuthMerchantApiController::class,'index'])->name('login');
    Route::post('login',[AuthMerchantApiController::class,'login']);
    Route::post('register',[AuthMerchantApiController::class,'register']);
    Route::group([
        // 'middleware' => 'auth:merchantAPI',
    ],function(){
        Route::get('login-data',[AuthMerchantApiController::class,'data']);
        Route::get('logout',[AuthMerchantApiController::class,'logout']);
    });
});
// Route::apiResource('/product', ProductController::class);
Route::apiResource('/withdraw', WithdrawController::class);
Route::apiResource('/product-type', ProductTypeController::class);
Route::apiResource('/order', OrderController::class);
Route::prefix('/order')->group(function(){
    Route::get('/{order}/cancel',[OrderController::class,'updatefail']);
    Route::get('/{order}/pay',[PaymentController::class,'store']);
    Route::get('/{order}/payment',[PaymentController::class,'index']);
    Route::get('/{order}/payment-history',[PaymentController::class,'history']);
    Route::get('/{order}/item',[OrderDetailController::class,'index']);
    Route::post('/{order}/item',[OrderDetailController::class,'store']);
    Route::put('/{order}/item/{detail}',[OrderDetailController::class,'update']);
    Route::delete('/{order}/item/{detail}',[OrderDetailController::class,'destroy']);
});
