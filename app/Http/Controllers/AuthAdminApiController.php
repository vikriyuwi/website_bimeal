<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Admin;
use App\Models\Topup;
use Illuminate\Support\Facades\Auth;
class AuthAdminApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:adminApi',[
            'except' => ['index','login']
        ]);
    }
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check the form validation",
                $validator->errors(),
                422
            );
        }

        $validated = $validator->validated();

        $token = null;
        if ($token = Auth::guard('adminApi')->attempt($validated)) {
            return $this->createNewToken($token);
        } else {
            return (new ApiRule)->responsemessage(
                "Unauthorized",
                null,
                401
            );
        }
    }
    public function data(Request $request)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $admin = Admin::find((string) $apy->sub);
        return response()->json($admin,200);
    }
    public function topupVerify(Request $request)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $validator = Validator::make(
            $request->all(),
            [
                'id'=>'required|exists:topups,id',
            ]
        );

        if ($validator->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check the form validation",
                $validator->errors(),
                422
            );
        }
        $validated = $validator->validate();
        
        $topup = Topup::find($validated['id']);

        $transaction = true;
        try {
            DB::transaction(function () use ($topup,$apy) {
                $data['status'] = "SUCCESS";
                $data['admin_id'] = (string) $apy->sub;
                $topup->update($data);
            });
        } catch (\Throwable $th) {
            $transaction = false;
        }
        if($transaction) {
            return (new ApiRule)->responsemessage(
                "Topup ".$topup->id." has been verified",
                $topup,
                422
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Internal server error",
                "",
                500
            );
        }
    }
    public function logout()
    {
        $token = Auth::getToken();
        try {
            Auth::invalidate($token);
            return (new ApiRule)->responsemessage(
                "Success logout",
                null,
                200
            );
        } catch (\Throwable $th) {
            return (new ApiRule)->responsemessage(
                "Fail logout",
                null,
                500
            );
        }
    }
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => auth('buyerApi')->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
