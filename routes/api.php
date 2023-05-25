<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\MerchantController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\TopupController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductController;
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

Route::apiResource('/account', AccountController::class);
Route::apiResource('/buyer', BuyerController::class);
Route::apiResource('/merchant', MerchantController::class);
Route::apiResource('/withdraw', WithdrawController::class);
Route::apiResource('/topup', TopupController::class);
Route::apiResource('/product-type', ProductTypeController::class);
Route::apiResource('/product', ProductController::class);
Route::apiResource('/order', OrderController::class);
Route::apiResource('/payment', PaymentController::class);
Route::prefix('/order')->group(function(){
    Route::get('/{order}/cancel',[OrderController::class,'updatefail']);
    Route::get('/{order}/pay',[PaymentController::class,'store']);
    Route::get('/{order}/payment',[PaymentController::class,'index']);
    Route::get('/{order}/payment-history',[PaymentController::class,'history']);
    Route::get('/{order}/detail',[OrderDetailController::class,'index']);
});
// Route::apiResource('/order-detail', OrderDetailController::class);
