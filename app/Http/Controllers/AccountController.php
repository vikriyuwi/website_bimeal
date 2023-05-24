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

    public function create(Request $request)
{
    $accounts = $request->validate([
        'username' => 'required|string',
        'password' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'role' => 'required|string',
        'verified_at' => 'required|date',
        'token' => 'required|string'
    ]);

    $newAccount = [
        'username' => $accounts['username'],
        'password' => $accounts['password'],
        'email' => $accounts['email'],
        'phone' => $accounts['phone'],
        'role' => $accounts['role'],
        'verified_at' => $accounts['verified_at'],
        'token' => $accounts['token']
    ];
    
    return response()->json($newAccount);
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

public function show(string $id)
{
    $account = Account::findOrFail($id);
    return response()->json($account);
}

public function edit(string $id)
{
    $account = Account::findOrFail($id);
    // Tampilkan tampilan form untuk mengedit data
}

public function update(Request $request, string $id)
{
    $account = Account::findOrFail($id);

    $validatedData = $request->validate([
        'username' => 'required|string',
        'email' => 'required|email',
        'phone' => 'required|string',
        'role' => 'required|string',
        'verified_at' => 'required|date',
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
