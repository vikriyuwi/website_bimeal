<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
use App\Models\Product;

class OrderDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $order)
    {
        $orderDetails = OrderDetail::with('product')->where('order_id','=',$order)->get();
        return (new ApiRule)->responsemessage(
            "Order details data",
            $orderDetails,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(string $order,Request $request)
    {
        $request['order_id'] = $order;
        $validation = Validator::make(
            $request->all(),
            [
                'order_id'=>'required|exists:orders,id',
                'product_id'=>'required|exists:products,id',
                'quantity'=>'required|numeric'
            ]
        );

        $stock = Product::find($request->product_id)->stock;
        if($request->quantity > $stock) {
            return (new ApiRule)->responsemessage(
                "Order reach the maximum stock",
                null,
                422
            );
        }

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $newOrderDetail = OrderDetail::create($validation->validated());
            if($newOrderDetail) {
                return (new ApiRule)->responsemessage(
                    "New order detail created",
                    $newOrderDetail,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New order detail fail to be created",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $orderDetail = OrderDetail::find($id);
        $orderDetail->product;
        if(!$orderDetail) {
            return (new ApiRule)->responsemessage(
                "Order detail data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order detail data found",
                $orderDetail,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $order,Request $request, string $id)
    {
        $orderDetail = OrderDetail::find($id);

        if(!$orderDetail) {
            return (new ApiRule)->responsemessage(
                "Order detail data not found",
                "",
                404
            );
        }

        if($orderDetail->order_id != $order) {
            return (new ApiRule)->responsemessage(
                "This product not in this order detail list",
                null,
                422
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'quantity'=>'required|numeric'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $stock = Product::find($orderDetail->product_id)->stock;
            if($request->quantity > $stock) {
                return (new ApiRule)->responsemessage(
                    "Order reach the maximum stock",
                    null,
                    422
                );
            }

            if($orderDetail->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Order data updated",
                    $orderDetail,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Order data fail to be updated",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $order,string $id)
    {
        $orderDetail = OrderDetail::find($id);

        if(!$orderDetail) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        }

        if($orderDetail->delete()) {
            return (new ApiRule)->responsemessage(
                "Order detail data deleted",
                $orderDetail,
                200
            );
        } else {
            if($orderDetail->order_id != $order) {
                return (new ApiRule)->responsemessage(
                    "This product not in this order detail list",
                    null,
                    422
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Order detail data fail to be deleted",
                    $orderDetail,
                    500
                );
            }
        }
    }
}
