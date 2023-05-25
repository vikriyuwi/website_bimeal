<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiRule;
use App\Models\BuyerBalance;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(string $order)
    {
        $payment = Order::find($order)->payments()->orderBy('updated_at','DESC')->first();
        return (new ApiRule)->responsemessage(
            "Payments data",
            $payment,
            200
        );
    }

    public function history(string $order)
    {
        $payment = Order::find($order)->payments()->orderBy('updated_at','DESC')->get();
        return (new ApiRule)->responsemessage(
            "Payments data",
            $payment,
            200
        );
    }

    public function countBalance(string $buyer)
    {
        $balance = BuyerBalance::where('buyer_id','=',$buyer)->first();
        return $balance->total_balance;
    }

    public function store(string $order)
    {
        $paymentActive = Payment::where('order_id','=',$order)->orderBy('updated_at','DESC')->first();
        
        if($paymentActive->status == 'PROCESS') {
            return (new ApiRule)->responsemessage(
                "Payment of this order has been paid",
                null,
                422
            );
        }

        $orderData = Order::find($order);

        if($orderData->status != 'NEW') {
            return (new ApiRule)->responsemessage(
                "Order data cannot be paid, please check the status of the order",
                null,
                422
            );
        }

        if($paymentActive) {
            $data['status'] = "FAIL";
            $paymentActive->update($data);
        }

        $orderDetails = $orderData->orderDetails;

        $transaction = true;
        foreach ($orderDetails as $d) {
            if ($d->quantity > $d->product->stock) {
                try {
                    DB::transaction(function () use ($orderData) {
                        $data['status'] = "FAIL";
                        $orderData->update($data);
                    });
                } catch (\Throwable $th) {
                    $transaction = false;
                }
                if($transaction) {
                    return (new ApiRule)->responsemessage(
                        "Order and payment canceled due the minimum stock",
                        null,
                        422
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

        $bill = 0;
        foreach ($orderDetails as $detail) {
            $orderDetail = OrderDetail::find($detail->id);
            $price = $orderDetail->product->price * $orderDetail->quantity;
            $bill += $price;
        }

        $buyer = Order::find($order)->buyer;
        $balance = $this->countBalance($buyer->id);
        if($balance < $bill) {
            return (new ApiRule)->responsemessage(
                "You have not enough balance",
                [
                    "your_balance"=>$balance,
                    "bill"=>$bill
                ],
                422
            );
        }

        $validated['bill'] = $bill;
        $validated['order_id'] = $order;
        $validated['status'] = "PROCESS";
        $newPayment = Payment::create($validated);
        if ($newPayment) {
            return (new ApiRule)->responsemessage(
                "New payment successfully created!",
                $validated,
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Failed to create new payment",
                null,
                500
            );
        }
    }

    public function show (string $id)
    {
        $payment = Payment::findOrFail($id);

        if(!$payment) {
            return (new ApiRule)->responsemessage(
                "Payment not found",
                null,
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Payment data",
                $payment,
                200
            );
        }
    }

    public function update(string $id)
    {
        $payment = Payment::find($id);

        if(!$payment) {
            return (new ApiRule)->responsemessage(
                "Payment data not found",
                null,
                404
            );
        }

        $status = "";
        switch ($payment->status) {
            case "PROCESS":
                $status = "SUCCESS";
                break;
            default:
                $status = $payment->status;
                break;
        }
        $validated['status'] = $status;
        if($payment->update($validated)) {
            return (new ApiRule)->responsemessage(
                "Payment data updated",
                $payment,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Payment data fail to be updated",
                $payment,
                500
            );
        }
    }

    public function updatefail(Request $request, string $id)
    {
        $payment = Payment::find($id);

        $validation = Validator::make(
            $request->all(),
            [
                'bill'=>'required|integer',
            ]
        );

        if(!$payment) {
            return (new ApiRule)->responsemessage(
                "Payment data not found",
                null,
                404
            );
        }

        if($payment->status == 'SUCCESS') {
            return (new ApiRule)->responsemessage(
                "Payment data status is SUCCESS and cannot be canceled",
                null,
                422
            );
        }

        $validated['status'] = "FAIL";
        if($payment->update($validated)) {
            return (new ApiRule)->responsemessage(
                "Payment data updated",
                $validation,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Payment data fail to be updated",
                $validation,
                500
            );
        }
    }

    public function destroy(string $id)
    {
        $payment = Payment::find($id);
        if (!$payment){
            return (new ApiRule)->responsemessage(
                "Payment data not found",
                null,
                404
            );
        }

        if ($payment -> delete()){
            return (new ApiRule)->responsemessage(
                "Payment data deleted",
                $payment,
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Payment data fail to be deleted",
                $payment,
                500
            );
        }
    }

}
