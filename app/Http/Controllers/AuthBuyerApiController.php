<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Buyer;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
class AuthBuyerApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:buyerAPI',[
            'except' => ['index','login','register']
        ]);
    }

    public function register(Request $request)
    {
        $messages = [
            'same' => 'The :attribute and :other must match.',
            'in' => 'The :attribute must be one of the following types: :values',
            'unique' => 'The :attribute is already registered',
        ];
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:8|unique:buyers,username',
            'password' => 'required|string|confirmed|min:6',
            'email' => 'required|email|unique:buyers,email',
            'phone' => 'required|string|unique:buyers,phone',
            'name' => 'required|string',
            'group' => 'required|string|in:STUDENT,LECTURER,STAFF,OTHER',
            'group_id' => 'required|unique:buyers,group_id'
        ],$messages);
        
        if ($validator->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check the form validation",
                $validator->errors(),
                422
            );
        } else {
            $validatedData = $validator->validated();
            $validatedData['password'] = bcrypt($validatedData['password']);
            $newAccount = Buyer::create($validatedData);
            if ($newAccount) {
                return (new ApiRule)->responsemessage(
                    "New account successfully created",
                    $newAccount,
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
        if ($token = Auth::guard('merchantAPI')->attempt($validated)) {
            return $this->createNewToken($token);
        } else {
            return (new ApiRule)->responsemessage(
                "Unauthorized",
                null,
                401
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

    public function index()
    {
        $data = [
            'login' => 'http://127.0.0.1:8000/api/buyer/login',
            'register' => 'http://127.0.0.1:8000/api/buyer/register',
        ];

        return (new ApiRule)->responsemessage(
            "Login required",
            $data,
            403
        );
    }
    public function data(Request $request)
    {
        $token = Auth::getToken();
        $apy = Auth::getPayload($token)->toArray();

        $buyer = Buyer::find($apy['sub']);
        return response()->json($buyer,200);
    }

    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('buyerAPI')->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
