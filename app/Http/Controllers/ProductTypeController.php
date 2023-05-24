<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductType;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
class ProductTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productTypes = ProductType::all();
        return (new ApiRule)->responsemessage(
            "Product Types data",
            $productTypes,
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
                'name'=>'required|string',
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $newProductType = ProductType::create($validation->validated());
            if($newProductType) {
                return (new ApiRule)->responsemessage(
                    "New product type created",
                    $newProductType,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "New product type fail to be created",
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
        $productType = ProductType::find($id);

        if(!$productType) {
            return (new ApiRule)->responsemessage(
                "Product type data not found",
                "",
                404
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Product type data found",
                $productType,
                200
            );
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $productType = ProductType::find($id);

        if(!$productType) {
            return (new ApiRule)->responsemessage(
                "Product type data not found",
                "",
                404
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'name'=>'required|string',
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($productType->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Product data updated",
                    $productType,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Product data fail to be updated",
                    "",
                    500
                );
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $productType = ProductType::find($id);

        if(!$productType) {
            return (new ApiRule)->responsemessage(
                "Product type data not found",
                "",
                404
            );
        }

        if($productType->delete()) {
            return (new ApiRule)->responsemessage(
                "Product type data deleted",
                $productType,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Product type data fail to be deleted",
                $productType,
                500
            );
        }
    }
}
