<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiRule;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::all();
        return (new ApiRule)->responsemessage(
            "Payments data",
            $payments,
            200
        );
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'order_id'=>'required|exists:orders,id',
        'bill'=>'required|numeric',
        'status'=>'required|string'
    ]);

    if ($validator->fails()) {
        return (new ApiRule)->responsemessage(
            "Please check your form",
            $validator->errors(),
            422
        );
    } else {    
        $newPayment = Payment::create($validatedData);
        
        if ($newPayment) {
            return (new ApiRule)->responsemessage(
                "New payment successfully created!",
                $validator,
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

    public function update(Request $request, string $id)
    {
        $payment = Payment::findOrFail($id);

        $validation = Validator::make(
            $request->all(),
            [
                'order_id'=>'required|exists:orders,id',
                'bill'=>'required|integer',
                'status'=>'required|string'
            ]
        );

        if(!$payment) {
            return (new ApiRule)->responsemessage(
                "Payment data not found",
                "",
                404
            );
        }

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($payment->update($validation)) {
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
    }

    public function destroy(string $id)
    {
        $payment = Payment::findOrFail($id);
        if ($payment){
            return (new ApiRule)->responsemessage(
                "Payment data not found",
                $payment,
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
