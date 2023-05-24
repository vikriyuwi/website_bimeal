<?php

namespace App\Http\Controllers;

use App\Models\Account;
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
    $validatedData = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'role' => 'required|string',
        'verified_at' => 'date',
        'token' => 'required|string'
    ]);

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
