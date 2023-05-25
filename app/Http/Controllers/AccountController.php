<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiRule;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return (new ApiRule)->responsemessage(
            "Accounts data",
            $accounts,
            200
        );
    }

    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'username' => 'required|string',
        'password' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'role' => 'required|in:BUYER,MERCHANT',
        'verified_at' => 'date',
        'token' => 'string'
    ]);

    if ($validator->fails()) {
        return (new ApiRule)->responsemessage(
            "Please check your form",
            $validator->errors(),
            422
        );
    } else {
        $validatedData = $validator->validated();
        $validatedData['password'] = bcrypt($validatedData['password']);
        
        $newAccount = Account::create($validatedData);
        
        if ($newAccount) {
            return (new ApiRule)->responsemessage(
                "New account successfully created!",
                $validator->validated(),
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Failed to create new account",
                null,
                500
            );
        }
    }
}


    public function show (string $id)
    {
        $account = Account::find($id);

        if(!$account) {
            return (new ApiRule)->responsemessage(
                "Account not found",
                null,
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Account data",
                $account,
                200
            );
        }
    }

    public function update(Request $request, string $id)
    {
        $account = Account::find($id);

        $validation = Validator::make(
            $request->all(),
            [
                'username'=>'required|string',
                'password'=>'required|string',
                'email'=>'required|email',
                'phone'=>'required|string',
                'role'=>'required|string',
                'verified_at'=>'date',
                'token'=>'required|string'
            ]
        );

        if(!$account) {
            return (new ApiRule)->responsemessage(
                "Account data not found",
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
            if($account->update($validation)) {
                return (new ApiRule)->responsemessage(
                    "Account data updated",
                    $validation,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Account data fail to be updated",
                    $validation,
                    500
                );
            }
        }
    }

    public function destroy(string $id)
    {
        $account = Account::find($id);

        if (!$account){
            return (new ApiRule)->responsemessage(
                "Account data not found",
                $account,
                404
            );
        }

        if ($account -> delete()){
            return (new ApiRule)->responsemessage(
                "Account data deleted",
                $account,
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Account data fail to be deleted",
                $account,
                500
            );
        }
    }

}
