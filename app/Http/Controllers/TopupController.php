<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Topup;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
class TopupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $topups = Topup::all();
        return (new ApiRule)->responsemessage(
            "Topups data",
            $topups,
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validation = Validator::make(
            $request->all(),
            [
                'buyer_id'=>'required|exists:buyers,id',
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
    public function show(string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Topup data found",
                $topup,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
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
    public function destroy(string $id)
    {
        $topup = Topup::find($id);

        if(!$topup) {
            return (new ApiRule)->responsemessage(
                "Topup data not found",
                "",
                404
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
