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
        return response()->json($merchants);
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
                'name'=>'required|string',
                'location_number'=>'required|string',
                'time_open'=>'required',
                'time_close'=>'required'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage("Unprocessable Entity","Please check your form",$validation->errors(),422);
        } else {
            return (new ApiRule)->responsemessage("Created","New merchant successfully created!",$validation,201);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $merchant = Merchant::findOrFail($id);

        $validatedData = $request->validate([
            'account_id'=>'required|exists:accounts,id',
            'name'=>'required|string',
            'location_number'=>'required|string',
            'time_open'=>'required',
            'time_close'=>'required'
        ]);

        $merchant->update($validatedData);
        return response()->json($merchant);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $merchant = Merchant::findOrFail($id);
        $merchant->delete();

        return response()->json(['message' => 'Account deleted successfully']);
    }
}