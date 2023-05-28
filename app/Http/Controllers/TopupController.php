<?php

namespace App\Http\Controllers;

use App\Models\BuyerBalance;
use Illuminate\Http\Request;
use App\Models\Topup;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BuyerBalanceReport;
class TopupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:buyerApi',);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $balance = DB::select("select * from `buyer_balances` where `buyer_id` = '".$apy->sub."' limit 1;");
        // $topups = BuyerBalance::where('buyer_id','=',$apy->sub)->first();
        return (new ApiRule)->responsemessage(
            "Topups data",
            $balance,
            200
        );
    }

    public function history()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $balance = BuyerBalanceReport::where('buyer_id','=',(string) $apy->sub)->get();
        // $balance = DB::select("select * from `buyer_balance_reports` where `buyer_id` = '".$apy->sub."';");
        // $topups = BuyerBalance::where('buyer_id','=',$apy->sub)->first();
        return (new ApiRule)->responsemessage(
            "Topups data",
            $balance,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $request['buyer_id'] = $apy->sub;

        $admin = Admin::first();

        // return $admin->id;
        // return response()->json($admin->id);

        $validation = Validator::make(
            $request->all(),
            [
                'debt'=>'required|numeric',
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $validated = $validation->validated();
            $validated['status'] = "PROCESS";
            $validated['buyer_id'] = $apy->sub;
            $validated['admin_id'] = (string) $admin->id;
            $newTopup = Topup::create($validated);
            if($newTopup) {
                return (new ApiRule)->responsemessage(
                    "New topup created",
                    $newTopup,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New topup fail to be created",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $buyer, string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
            );
        } else {
            if($topup->buyer_id == $buyer) {
                return (new ApiRule)->responsemessage(
                    "Topup data found",
                    $topup,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Topup data not own by this buyer",
                    null,
                    200
                );
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $buyer, Request $request, string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
            );
        }

        if($topup->buyer_id != $buyer) {
            return (new ApiRule)->responsemessage(
                "Topup data not own by this buyer",
                null,
                200
            );
        }

        $validated['status'] = $topup->status;
        switch ($topup->status) {
            case 'PROCESS':
                $validated['status'] = "SUCCESS";
                break;
            default:
                break;
        }
        if($topup->update($validated)) {
            return (new ApiRule)->responsemessage(
                "Topup data updated",
                $topup,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Topup data fail to be updated",
                "",
                500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $buyer, string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
            );
        }

        if($topup->buyer_id != $buyer) {
            return (new ApiRule)->responsemessage(
                "Topup data not own by this buyer",
                null,
                200
            );
        }

        if($topup->delete()) {
            return (new ApiRule)->responsemessage(
                "Topup data deleted",
                $topup,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Topup data fail to be deleted",
                $topup,
                500
            );
        }
    }
}
