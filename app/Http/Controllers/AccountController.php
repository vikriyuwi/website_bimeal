<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return response()->json($accounts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'role' => 'required|string',
            'verified_at' => 'date',
            'token' => 'required|string'
        ]);
       
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $validatedData = $validator->validated();
        $validatedData['password'] = bcrypt($validatedData['password']);
    
        $newAccount = Account::create($validatedData);
    
        return response()->json($newAccount, 201);
    }
    
    

public function update(Request $request, string $id)
{
    $account = Account::findOrFail($id);

    $validatedData = $request->validate([
        'username' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'role' => 'required|string',
        'verified_at' => 'date',
        'token' => 'required|string'
    ]);

    $account->update($validatedData);

    return response()->json($account);
}


public function destroy(string $id)
{
    $account = Account::findOrFail($id);
    $account->delete();

    return response()->json(['message' => 'Account deleted successfully']);
}

}
