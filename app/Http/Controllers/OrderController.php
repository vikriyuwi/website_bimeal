<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Http\Controllers\ApiRule;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\BuyerBalance;
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

    public function indexMerchant()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $orders = Order::with('orderDetails')->where('merchant_id','=',(string) $apy->sub)->get();

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

        $bill = 0;

        foreach($order->orderDetails as $detail)
        {
            $bill += $detail->total_price;
        }

        $order['bill'] = $bill;

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
        $order = Order::with('orderDetails')->find($id);
        
        // existing check
        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
                404
            );
        }

        // merchant check
        if($order->merchant_id != (string) $apy->sub) {
            return (new ApiRule)->responsemessage(
                "Order is not in your merchant",
                null,
                422
            );
        }

        // status check
        if($order->status != "PAID" && $order->status != "PROCESS") {
            return (new ApiRule)->responsemessage(
                "Update fail due the order status",
                [
                    'order_status' => $order->status
                ],
                422
            );
        }

        // payment check
        $payment = Order::find($id)->payments()->orderBy('updated_at','DESC')->first();
        if(!$payment) {
            return (new ApiRule)->responsemessage(
                "Order is not paid",
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

        // availability check
        $details = OrderDetail::with('product')->where('order_id','=',$id)->get();
        $unavailable = [];
        $inactive = [];
        $total = 0;
        foreach ($details as $detail) {
            $d = OrderDetail::find($detail->id);
            $product = Product::find($detail->product_id);
            $data['total_price'] = $product->price * $detail->quantity;
            $d->update($data);

            if(!$product->is_available)
            {
                array_push($unavailable,$product->name);
            }

            if(!$product->is_active)
            {
                array_push($inactive,$product->name);
            }

            $total = $total + $data['total_price'];
        }

        if($unavailable || $inactive)
        {
            // update data
            $transaction = true;
            try {
                DB::transaction(function () use ($order,$payment) {
                    $data['status'] = "FAIL";
                    $order->update($data);
                    $payment->update($data);
                });
            } catch (\Throwable $th) {
                $transaction = false;
            }

            // return data
            if($transaction)
            {
                if($inactive)
                {
                    return (new ApiRule)->responsemessage(
                        "Order failed due the inactive product",
                        $inactive,
                        409
                    );
                } else if ($unavailable)
                {
                    return (new ApiRule)->responsemessage(
                        "Order failed due the unvailable stock",
                        $unavailable,
                        409
                    );
                }
            }
        }

        $newStatus = "";        
        switch ($order->status) {
            case "PAID":
                $newStatus = "PROCESS";
                break;
            case "PROCESS":
                $newStatus = "PICKUP";
                break;
            default:
                $newStatus = $order->status;
                break;
        }

        $transaction = true;

        try {
            DB::transaction(function () use ($order,$newStatus) {
                // order status
                $data = [
                    'status' => $newStatus,
                ];

                // order pickup code
                if ($newStatus == 'PICKUP') {
                    $data['code'] = $this->generatePin();
                }

                $order->update($data);
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
                null,
                500
            );
        }
    }

    public function cancel(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $order = Order::with('orderDetails')->find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "There are no order data to cancel",
                null,
                404
            );
        }

        if($order->buyer_id != $apy->sub) {
            return (new ApiRule)->responsemessage(
                "The order is not own by you",
                null,
                404
            );
        }

        $payment = Payment::where('order_id','=',$order->id)->where('status','=','SUCCESS')->orderBy('updated_at','DESC')->first();
        $data['status'] = 'FAIL';

        $transaction = true;

        try {
            DB::transaction(function () use ($order,$payment,$data) {
                if($payment) {
                    $payment->update($data);
                }
                $order->update($data);
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }

        if($transaction) {
            return (new ApiRule)->responsemessage(
                "Order data has been canceled",
                $order,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order data fail to cancel",
                "",
                500
            );
        }
    }

    public function cancelByMerchant(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $order = Order::with('orderDetails')->find($id);

        if(!$order) {
            return (new ApiRule)->responsemessage(
                "There are no order data to cancel",
                null,
                404
            );
        }

        if($order->merchant_id != $apy->sub) {
            return (new ApiRule)->responsemessage(
                "The order is not own by your merchant",
                null,
                404
            );
        }

        if($order->status != 'NEW' && $order->status != 'PAID') {
            return (new ApiRule)->responsemessage(
                "Order cannot be canceled due the order status",
                $order,
                422
            );
        }

        $payment = Payment::where('order_id','=',$order->id)->where('status','=','SUCCESS')->orderBy('updated_at','DESC')->first();
        $data['status'] = 'CANCELED';

        $transaction = true;

        try {
            DB::transaction(function () use ($order,$payment,$data) {
                if($payment) {
                    $payment->update($data);
                }
                $order->update($data);
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }

        if($transaction) {
            return (new ApiRule)->responsemessage(
                "Order data has been canceled",
                $order,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order data fail to cancel",
                "",
                500
            );
        }
    }

    public function pay(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();
        $order = Order::find($id);

        if(!$order)
        {
            return (new ApiRule)->responsemessage(
                "Order not found",
                null,
                404
            );
        }

        if($order->buyer_id != $apy->sub)
        {
            return (new ApiRule)->responsemessage(
                "Order is not own by you",
                null,
                422
            );
        }

        $diffTime = Carbon::createFromFormat('Y-m-d H:i:s', $order->created_at)->diffInMinutes(Carbon::now());
        $details = $order->orderDetails;

        if($order->status != 'NEW')
        {
            return (new ApiRule)->responsemessage(
                "Order paid fail due the order status",
                $order,
                422
            );
        }

        $payment = Payment::where('order_id','=',$id)->where('status','=','SUCCESS')->orderBy('updated_at','DESC')->first();

        if($payment)
        {
            return (new ApiRule)->responsemessage(
                "Order has been paid",
                null,
                422
            );
        }

        if($diffTime > 30) {
            $data['status'] = 'EXPIRED';
            $order->update($data);
            return (new ApiRule)->responsemessage(
                "Order expired",
                null,
                422
            );
        }
        
        $unavailable = [];
        $inactive = [];
        $total = 0;
        foreach ($details as $detail) {
            $d = OrderDetail::find($detail->id);
            $product = Product::find($detail->product_id);

            $data['total_price'] = $product->price * $detail->quantity;
            $d->update($data);

            if(!$product->is_available)
            {
                array_push($unavailable,$product->name);
            }

            if(!$product->is_active)
            {
                array_push($inactive,$product->name);
            }

            $total = $total + $data['total_price'];
        }

        if($unavailable)
        {
            $data = [
                'status' => 'FAIL'
            ];
            $order->update($data);
            return (new ApiRule)->responsemessage(
                "Order failed due the unvailable stock",
                $unavailable,
                409
            );
        }

        if($inactive)
        {
            $data = [
                'status' => 'FAIL'
            ];
            $order->update($data);
            return (new ApiRule)->responsemessage(
                "Order failed due the inactive product",
                $inactive,
                409
            );
        }

        $balance = BuyerBalance::where('buyer_id','=',(string) $apy->sub)->first();

        if($total > $balance->balance)
        {
            $data = [
                'your_balance' => $balance->balance,
                'bill' => $total,
            ];
            return (new ApiRule)->responsemessage(
                "Payment failed due the minimum balance",
                $data,
                422
            );
        }

        $transaction = true;
        
        try {
            DB::transaction(function () use ($order,$total) {
                $data['status'] = 'PAID';
                $order->update($data);

                Payment::create([
                    'order_id' => $order->id,
                    'bill' => $total,
                    'status' => 'SUCCESS'
                ]);
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }

        if($transaction) {
            return (new ApiRule)->responsemessage(
                "Order data has been paid",
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

    private function generatePin($digits = 6)
    {
        $i = 0;
        $pin = "";

        while ($i < $digits) {
            $pin .= mt_rand(0, 9);
            $i++;
        }

        return $pin;
    }

    public function pickup(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();
        $order = Order::with('orderDetails')->find($id);
        
        // existing check
        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
                404
            );
        }

        // buyer check
        if($order->buyer_id != (string) $apy->sub) {
            return (new ApiRule)->responsemessage(
                "Order is not in yours",
                null,
                422
            );
        }

        // status check
        if($order->status != "PICKUP") {
            return (new ApiRule)->responsemessage(
                "Your order is not ready yet",
                [
                    'order_status' => $order->status
                ],
                422
            );
        }

        return (new ApiRule)->responsemessage(
            "Order detail",
            $order,
            200
        );
    }

    public function serveOrder(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();
        $order = Order::with('orderDetails')->find($id);
        
        // existing check
        if(!$order) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                null,
                404
            );
        }

        // buyer check
        if($order->merchant_id != (string) $apy->sub) {
            return (new ApiRule)->responsemessage(
                "Order is not in your merchant",
                null,
                422
            );
        }

        // status check
        if($order->status != "PICKUP") {
            return (new ApiRule)->responsemessage(
                "Your order is not ready yet",
                [
                    'order_status' => $order->status
                ],
                422
            );
        }

        return (new ApiRule)->responsemessage(
            "Order detail",
            $order,
            200
        );

        try {
            DB::transaction(function () use ($order) {
                $data['status'] = 'DONE';
                $order->update($data);
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }

        if($transaction) {
            return (new ApiRule)->responsemessage(
                "Order is done",
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
