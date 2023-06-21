<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
use Illuminate\Support\Facades\Auth;
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:merchantApi')->except(['all']);
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $products = Product::with('productType')->where('merchant_id','=',(string) $apy->sub)->get();
        return (new ApiRule)->responsemessage(
            "Products data",
            $products,
            200
        );
    }

    public function all()
    {
        $products = Product::with('productType')->get();
        return (new ApiRule)->responsemessage(
            "Products data",
            $products,
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

        $validation = Validator::make(
            $request->all(),
            [
                'product_type_id'=>'required|exists:product_types,id',
                'name'=>'required|string',
                'price'=>'required|numeric',
            ]
        );
        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        }

        $validated = $validation->validated();
        $validated['merchant_id'] = (string) $apy->sub;
        $newProduct = Product::create($validated);
        if($newProduct) {
            return (new ApiRule)->responsemessage(
                "New product created",
                $newProduct,
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "New product fail to be created",
                "",
                500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $product = Product::find($id);

        if(!$product) {
            return (new ApiRule)->responsemessage(
                "Product data not found",
                "",
                404
            );
        } else {
            if($product->merchant_id == $apy->sub) {
                return (new ApiRule)->responsemessage(
                    "Product data found",
                    $product,
                    200
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Product data not own by this merchant",
                    null,
                    200
                );
            }
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $product = Product::find($id);

        if(!$product) {
            return (new ApiRule)->responsemessage(
                "Product data not found",
                "",
                404
            );
        }

        if($product->merchant_id != $apy->sub) {
            return (new ApiRule)->responsemessage(
                "Product data not own by this merchant",
                null,
                200
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'product_type_id'=>'exists:product_types,id',
                'name'=>'string',
                'price'=>'numeric',
                'is_available'=>'boolean',
                'is_active'=>'boolean'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            if($product->update($validation->validated())) {
                return (new ApiRule)->responsemessage(
                    "Product data updated",
                    $product,
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
}
