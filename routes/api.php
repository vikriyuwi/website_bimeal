<?php

use App\Http\Controllers\TopupController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthBuyerApiController;
use App\Http\Controllers\AuthMerchantApiController;
use App\Http\Controllers\AuthAdminApiController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderDetailController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Models\Product;

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


Route::get('/',[AuthBuyerApiController::class,'index'])->name('login');
Route::post('login',[AuthBuyerApiController::class,'login']);
Route::post('register',[AuthBuyerApiController::class,'register']);
Route::get('login-data',[AuthBuyerApiController::class,'data']);
Route::get('logout',[AuthBuyerApiController::class,'logout']);

Route::group([
    'prefix' => 'product'
], function() {
    Route::get('/',[ProductController::class,'all']);
});

Route::group([
    'prefix' => 'balance'
],function(){
    Route::get('/',[TopupController::class,'index']);
    Route::get('/history',[TopupController::class,'history']);
    Route::post('/topup',[TopupController::class,'store']);
});

Route::group([
    'middleware' => 'auth:buyerApi',
    'prefix' => 'order'
],function(){
    Route::get('/',[OrderController::class,'index']);
    Route::get('/now',[OrderController::class,'activeOrder']);
    Route::post('/add',[OrderDetailController::class,'store']);
    Route::put('/{orderDetail}',[OrderDetailController::class,'update']);
    Route::delete('/{orderDetail}',[OrderDetailController::class,'destroy']);
    Route::get('{order}/pay',[OrderController::class,'pay']);
    Route::get('{order}/cancel',[OrderController::class,'cancel']);
});

Route::group([
    'prefix' => 'admin'
], function($router) {
    Route::get('/',[AuthAdminApiController::class,'index']);
    Route::post('topup-verify',[AuthAdminApiController::class,'topupVerify']);
    Route::post('login',[AuthAdminApiController::class,'login']);
    Route::post('register',[AuthAdminApiController::class,'register']);
    Route::get('login-data',[AuthAdminApiController::class,'data']);
    Route::get('logout',[AuthAdminApiController::class,'logout']);
});

Route::group([
    'prefix' => 'merchant'
], function($router) {
    Route::get('/',[AuthMerchantApiController::class,'index']);
    Route::post('login',[AuthMerchantApiController::class,'login']);
    Route::post('register',[AuthMerchantApiController::class,'register']);
    Route::get('login-data',[AuthMerchantApiController::class,'data']);
    Route::get('logout',[AuthMerchantApiController::class,'logout']);
    Route::apiResource('/product',ProductController::class,['except'=>['destroy']]);
    Route::group([
        'middleware' => 'auth:merchantApi'
    ], function(){
        Route::get('order',[OrderController::class,'indexMerchant']);
        Route::get('order/{order}/cancel',[OrderController::class,'cancelByMerchant']);
        Route::get('order/{order}/update',[OrderController::class,'update']);
    });
});
// Route::apiResource('/product', ProductController::class);
// Route::apiResource('/withdraw', WithdrawController::class);
// Route::apiResource('/product-type', ProductTypeController::class);
// Route::prefix('/order')->group(function(){
//     Route::get('/{order}/cancel',[OrderController::class,'updatefail']);
//     Route::get('/{order}/pay',[PaymentController::class,'store']);
//     Route::get('/{order}/payment',[PaymentController::class,'index']);
//     Route::get('/{order}/payment-history',[PaymentController::class,'history']);
//     Route::get('/{order}/item',[OrderDetailController::class,'index']);
//     Route::post('/{order}/item',[OrderDetailController::class,'store']);
//     Route::put('/{order}/item/{detail}',[OrderDetailController::class,'update']);
//     Route::delete('/{order}/item/{detail}',[OrderDetailController::class,'destroy']);
// });
