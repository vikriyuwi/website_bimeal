<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $orders = Order::all();
        return (new ApiRule)->responsemessage(
            "Orders data",
            $orders,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'buyer_id'=>'required|exists:buyers,id',
                'status'=>'required|in:NEW,PROCESS,DONE, CANCELED'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $newOrder = Order::create($validation->validated());
            if($newOrder) {
                return (new ApiRule)->responsemessage(
                    "New order created",
                    $newOrder,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New order fail to be created",
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
        $order = Order::find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order data found",
                $order,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'status'=>'required|in:NEW,PROCESS,DONE, CANCELED'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($order->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Order data updated",
                    $order,
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
    public function destroy(string $id)
    {
        $order = Order::find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        }

        if($order->delete()) {
            return (new ApiRule)->responsemessage(
                "Order data deleted",
                $order,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order data fail to be deleted",
                $order,
                500
            );
        }
    }
}
