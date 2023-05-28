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
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $orders = Order::with('orderDetails')->where('buyer_id','=',(string) $apy->sub)->get();
        return (new ApiRule)->responsemessage(
            "Orders data",
            $orders,
            200
        );
    }

    public function activeOrder()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $order = Order::with('orderDetails')->where('buyer_id','=',(string) $apy->sub)->where('status','=','NEW')->orderBy('created_at','DESC')->first();

        if($order == null)
        {
            return (new ApiRule)->responsemessage(
                "No active order",
                null,
                404
            );
        }

        return (new ApiRule)->responsemessage(
            "Active order data",
            $order,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $newOrder = Order::create([
            'buyer_id' => (string) $apy->sub,
            'status' => 'NEW'
        ]);

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
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $order = Order::find($id)->with('orderDetails');
        
        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
                404
            );
        }

        if($order->merchant_id != (string) $apy->sub) {
            return (new ApiRule)->responsemessage(
                "Order is not in your merchant",
                null,
                422
            );
        }

        if($order->status == 'FAIL') {
            return (new ApiRule)->responsemessage(
                "Cannot process due the order is has been canceled",
                null,
                422
            );
        }

        $pay = Order::find($id)->payments()->orderBy('updated_at','DESC')->first();
        $payment = Payment::find($pay->id);

        if($payment->status == 'FAIL') {
            return (new ApiRule)->responsemessage(
                "Last payment failed",
                null,
                422
            );
        }

        $transaction = true;

        $details = OrderDetail::with('product')->where('order_id','=',$id)->get();

        $reachStock = false;
        foreach ($details as $d) {
            if ($d->quantity > $d->product->stock) {
                $reachStock = true;
                break;
            }
        }

        if($reachStock) {
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
    public function cancel()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $order = Order::where('buyer_id','=',(string) $apy->sub)->where('status','=','NEW')->orderBy('created_at','DESC')->first();

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "There are no order data to cancel",
                null,
                404
            );
        }

        $data['status'] = 'FAIL';
        $order->update($data);

        return (new ApiRule)->responsemessage(
            "Order has been canceled",
            $order,
            200
        );
    }
    public function updatefail(string $id)
    {
        $order = Order::find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
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
