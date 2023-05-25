<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buyers = Buyer::all();
        return (new ApiRule)->responsemessage(
            "Buyers data",
            $buyers,
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
                'account_id'=>'required|exists:accounts,id',
                'name' => 'required|string',
                'group' => 'required|string|in:STUDENT,LECTURER,STAFF,OTHER',
                'group_id' => 'required'
            ]
        );
        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $newBuyer = Buyer::create($validation->validated());
            if($newBuyer) {
                return (new ApiRule)->responsemessage(
                    "New buyer created",
                    $newBuyer,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New buyer fail to be created",
                    "",
                    500
                );
            }
        }
    }

    public function show(string $id)
    {
        $buyer = Buyer::find($id);

        if(!$buyer) {
            return (new ApiRule)->responsemessage(
                "Buyer data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Buyer data found",
                $buyer,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $buyer = Buyer::find($id);

        if(!$buyer) {
            return (new ApiRule)->responsemessage(
                "Buyer data not found",
                "",
                404
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'name' => 'required|string',
                'group' => 'required|string',
                'group_id' => 'required'
            ]
        );
        
        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($buyer->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Buyer data updated",
                    $buyer,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Buyer data fail to be updated",
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
        $buyer = Buyer::find($id);

        if(!$buyer) {
            return (new ApiRule)->responsemessage(
                "Buyer data not found",
                "",
                404
            );
        }

        if($buyer->delete()) {
            return (new ApiRule)->responsemessage(
                "Buyer data deleted",
                $buyer,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Buyer data fail to be deleted",
                $buyer,
                500
            );
        }
    }
}
