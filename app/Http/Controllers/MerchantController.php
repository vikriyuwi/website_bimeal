<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $merchants = Merchant::all();
        return response()->json($merchants)->header('Content-Type','application/json');
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $merchants = $request->validate([
            'account_id'=>'required|exists:accounts,id',
            'name'=>'required|string',
            'location_number'=>'required|string',
            'time_open'=>'required',
            'time_close'=>'required'
        ]);

        $newMerchant = Merchant::create($merchants);
        return response()->json($newMerchant,201)->header('Content-Type','application/json');
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
        return response()->json($merchant)->header('Content-Type','application/json');
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