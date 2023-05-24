<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\Account;
use Illuminate\Http\Request;

class BuyerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $buyers = Buyer::all();
        return response()->json($buyers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $buyers = $request->validate([
            'account_id'=>'request|exist:accounts,id',
            'name' => 'required|string',
            'group' => 'required|string',
            'group_id' => 'required|email'
        ]);
        $newBuyer = Buyer::create($buyers, 201);
        
        return response()->json($newBuyer);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Buyer $buyer)
    {
        $buyer = Buyer::findOrFail($id);

        $validatedBuyers = $request->validate([
            'account_id'=>'request|exist:accounts,id',
            'name' => 'required|string',
            'group' => 'required|string',
            'group_id' => 'required|email'
        ]);
        $buyer->update($validatedBuyers);
        
        return response()->json($buyer);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Buyer $buyer)
    {
        $buyer = Buyer::findOrFail($id);
        $buyer->delete();

        return response()->json(['message' => 'Buyer deleted successfully']);
    }
}
