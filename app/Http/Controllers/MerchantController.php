<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $merchants = Merchant::all();
        return (new ApiRule)->responsemessage(
            "Merchants data",
            $merchants,
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
                'username' => 'required|string',
                'password' => 'required|string',
                'email'=> 'required|string',
                'phone' => 'required|string',
                'verified_at' => 'date',
                'token' => 'string',
                'name' => 'required|string',
                'location_number' => 'required|string',
                'time_open' => 'required|string',
                'time_close' => 'required|string'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            // $newMerchant = Merchant::create($validation->validated());
            // $newMerchant['password'] = bcrypt($newMerchant['password']);

            $validatedData = $validation->validated();
            $validatedData['password'] = bcrypt($validatedData['password']);

            $newMerchant = Merchant::create($validatedData);

            if($newMerchant) {
                return (new ApiRule)->responsemessage(
                    "New merchant created",
                    $newMerchant,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New merchant fail to be created",
                    "",
                    500
                );
            }
        }
    }

    public function show(string $id)
    {
        $merchant = Merchant::find($id);

        if(!$merchant) {
            return (new ApiRule)->responsemessage(
                "Merchant data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Merchant data found",
                $merchant,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $merchant = Merchant::find($id);

        $validation = Validator::make(
            $request->all(),
            [
                'name' => 'string',
                'location_number' => 'string',
                'time_open' => 'string',
                'time_close' => 'string'
            ]
        );

        if(!$merchant) {
            return (new ApiRule)->responsemessage(
                "Merchant data not found",
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
            if($merchant->update($validation->validated())) {
                return (new ApiRule)->responsemessage(

                    "Merchant data updated",
                    $merchant,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Merchant data fail to be updated",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
}
