<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Withdraw;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
class WithdrawController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $merchant)
    {
        $withdraws = Withdraw::where('merchant_id','=',$merchant)->get();
        if(!$withdraws) {
            return (new ApiRule)->responsemessage(
                "Withdraws data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Withdraws data found",
                $withdraws,
                200
            );
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(string $merchant,Request $request)
    {
        $request['merchant_id'] = $merchant;
        $validation = Validator::make(
            $request->all(),
            [
                'merchant_id'=>'required|exists:merchants,id',
                'credit'=>'required|numeric',
                'status'=>'required|in:PROCESS,SUCCESS,FAIL',
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $newWithdraw = Withdraw::create($validation->validated());
            if($newWithdraw) {
                return (new ApiRule)->responsemessage(
                    "New withdraw created",
                    $newWithdraw,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New withdraw fail to be created",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $merchant,string $id)
    {
        $withdraw = Withdraw::find($id);

        if(!$withdraw) {
            return (new ApiRule)->responsemessage(
                "Withdraw data not found",
                "",
                404
            );
        } else {
            if($withdraw->merchant_id == $merchant) {
                return (new ApiRule)->responsemessage(
                    "Withdraw data found",
                    $withdraw,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Withdraw data not own by this merchant",
                    null,
                    422
                );
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $merchant, Request $request, string $id)
    {
        $withdraw = Withdraw::find($id);

        if($withdraw->merchant_id != $merchant) {
            return (new ApiRule)->responsemessage(
                "Withdraw data is not own by this merchant",
                null,
                422
            );
        }    

        $validation = Validator::make(
            $request->all(),
            [
                'credit'=>'required|numeric',
                'status'=>'required|in:PROCESS,SUCCESS,FAIL',
            ]
        );
        
        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($withdraw->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Withdraw data updated",
                    $withdraw,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Withdraw data fail to be updated",
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
        $withdraw = Withdraw::find($id);

        if(!$withdraw) {
            return (new ApiRule)->responsemessage(
                "Withdraw data not found",
                "",
                404
            );
        }

        if($withdraw->delete()) {
            return (new ApiRule)->responsemessage(
                "Withdraw data deleted",
                $withdraw,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Withdraw data fail to be deleted",
                $withdraw,
                500
            );
        }
    }
}
