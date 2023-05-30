<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ApiRule;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
class OrderDetailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:buyerApi');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(string $order)
    {
        $orderDetails = OrderDetail::with('product')->where('order_id','=',$order)->get();
        return (new ApiRule)->responsemessage(
            "Order details data",
            $orderDetails,
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
                'product_id'=>'required|exists:products,id',
                'quantity'=>'required|numeric'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        }

        $oldOrder = true;
        $order = Order::where('buyer_id','=',(string) $apy->sub)->where('status','=','NEW')->orderBy('created_at','DESC')->first();
        
        $product = Product::find($request->product_id);

        if($order == null)
        {
            $order = Order::create([
                'buyer_id' => (string) $apy->sub,
                'merchant_id' => $product->merchant_id,
                'status' => 'NEW'
            ]);
            $oldOrder = false;
        }

        if($oldOrder) {
            if($product->merchant_id != $order->merchant_id) {
                return (new ApiRule)->responsemessage(
                    "Order in multiple merchant is not allowed",
                    null,
                    422
                );
            }
        }

        if(!$product->is_active)
        {
            $data = [
                'status' => 'FAIL'
            ];
            $order->update($data);
            return (new ApiRule)->responsemessage(
                "Order failed due the inactive product",
                $product,
                409
            );
        }

        if(!$product->is_available)
        {
            $data = [
                'status' => 'FAIL'
            ];
            $order->update($data);
            return (new ApiRule)->responsemessage(
                "Order failed due the unvailable stock",
                $product,
                409
            );
        }

        $validated = $validation->validated();
        $validated['order_id'] = $order->id;
        $validated['total_price'] = $product->price * $validated['quantity'];

        $newOrderDetail = OrderDetail::create($validated);
        $newOrderDetail = OrderDetail::with('product')->find($newOrderDetail->id);
        if($newOrderDetail) {
            return (new ApiRule)->responsemessage(
                "New order detail created",
                $newOrderDetail,
                201
            );
        } else {
            return (new ApiRule)->responsemessage(
                "New order detail fail to be created",
                "",
                500
            );
        }
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $orderDetail = OrderDetail::find($id);

        if(!$orderDetail) {
            return (new ApiRule)->responsemessage(
                "Order detail data not found",
                "",
                404
            );
        }

        if($orderDetail->order->buyer_id != $apy->sub) {
            return (new ApiRule)->responsemessage(
                "The order is not own by you",
                null,
                422
            );
        }

        $order = Order::find($orderDetail->order->id);

        if($order->status != 'NEW') {
            return (new ApiRule)->responsemessage(
                "Update fail",
                "The order status is already in ".$order->status,
                422
            );
        }

        $validation = Validator::make(
            $request->all(),
            [
                'quantity'=>'required|numeric'
            ]
        );

        if($validation->fails()) {
            return (new ApiRule)->responsemessage(
                "Please check your form",
                $validation->errors(),
                422
            );
        } else {
            $product = Product::find($orderDetail->product_id);
            if($request->quantity > $product->stock) {
                return (new ApiRule)->responsemessage(
                    "Order reach the maximum stock",
                    null,
                    422
                );
            }

            $validated = $validation->validated();
            $validated['total_price'] = $product->price * $validated['quantity'];
            if($orderDetail->update($validated)) {
                return (new ApiRule)->responsemessage(
                    "Order data updated",
                    $orderDetail,
                    201
                );
            } else {
                return (new ApiRule)->responsemessage(
                    "Order data fail to be updated",
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
        $token = Auth::getToken();
        $apy = (object) Auth::getPayload($token)->toArray();

        $orderDetail = OrderDetail::find($id);

        if(!$orderDetail) {
            return (new ApiRule)->responsemessage(
                "Order data not found",
                "",
                404
            );
        }

        if($orderDetail->order->buyer_id != $apy->sub) {
            return (new ApiRule)->responsemessage(
                "The order is not own by you",
                null,
                422
            );
        }

        $order = Order::find($orderDetail->order_id);

        if($order->status != 'NEW') {
            return (new ApiRule)->responsemessage(
                "Deleted fail",
                "The order status is already in ".$order->status,
                422
            );
        }

        if($orderDetail->delete()) {
            if($order->orderDetails()->count() < 1) {
                $order->delete();
            }
            return (new ApiRule)->responsemessage(
                "Order detail data deleted",
                $orderDetail,
                200
            );
        } else {
            return (new ApiRule)->responsemessage(
                "Order detail data fail to be deleted",
                $orderDetail,
                500
            );
        }
    }
}
