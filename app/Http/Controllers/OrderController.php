<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

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
    public function store(Request $request,$id)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'buyer_id'=>'required|exists:buyers,id',
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $validated = $validation->validated();
            $validated['status'] = "NEW";
            $newOrder = Order::create($validated);
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
            $order->orderDetails;
            $payment = $order->payments()->orderBy('updated_at','DESC')->first();
            // $order['detail'] = $detail;
            $order['payment'] = $payment;
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
    public function update(string $id)
    {
        $order = Order::find($id);
        $pay = Order::find($id)->payments()->orderBy('updated_at','DESC')->first();
        $payment = Payment::find($pay->id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
                404
            );
        }

        if($order->status == 'FAIL') {
            return (new ApiRule)->responsemessage(
                "Cannot process due the order canceled",
                null,
                422
            );
        }

        if($payment->status == 'FAIL') {
            return (new ApiRule)->responsemessage(
                "Last payment failed",
                null,
                422
            );
        }

        $transaction = true;

        $details = OrderDetail::with('product')->where('order_id','=',$id)->get();
        foreach ($details as $d) {
            if ($d->quantity > $d->product->stock) {
                try {
                    DB::transaction(function () use ($order,$payment) {
                        $data['status'] = "FAIL";

                        $order->update($data);
                        $payment->update($data);
                    });
                } catch (\Throwable $th) {
                    $transaction = false;
                }
                if($transaction) {
                    return (new ApiRule)->responsemessage(
                        "Order and payment canceled due the minimum stock",
                        null,
                        200
                    );
                } else {
                    return (new ApiRule)->responsemessage(
                        "Server error",
                        "",
                        500
                    );
                }
            }
        }

        $newStatus = "";
        switch ($order->status) {
            case "NEW":
                $newStatus = "PROCESS";
                break;
            case "PROCESS":
                $newStatus = "DONE";
                break;
            default:
                $newStatus = $order->status;
                break;
        }

        try {
            DB::transaction(function () use ($order,$details,$newStatus,$payment) {
                foreach ($details as $d) {
                    $product = Product::find($d->product->id);
                    $data['stock'] = $product->stock - $d->quantity;
                    $product->update($data);
                }

                $data['status'] = $newStatus;
                $order->update($data);
                if($newStatus == 'PROCESS')
                {
                    $data['status'] = 'SUCCESS';
                    $payment->update($data);
                }
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }

        if($transaction) {
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
    public function updatefail(string $id)
    {
        $order = Order::find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        }
        $validated['status'] = "FAIL";
        if($order->update($validated)) {
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
