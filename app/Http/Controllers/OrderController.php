<?php

namespace App\Http\Controllers;

use App\Classes\JWT;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function index()
    {
        $user = JWT::get();
        if($user === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $orders = Order::where('user_id', $user['id'])->get();
        $res = [];
        foreach ($orders as $order) {
            $res[] = [
                'id'            => $order->id,
                'address'       => $order->address,
                'price'         => $order->price,
                'status'        => $order->status,
                'created_at'    => $order->created_at,
                'detailUrl' => route('detail', ['id' => $order->id]),
            ];
        }
        return response()->json([
            'status'    => 1,
            'orders'    => $res,
        ], 200);
    }


    public function create(Request $request)
    {
        $user = JWT::get();
        if($user === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $products = $request->json('products');
        if(empty($products)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Products must be specified.',
            ], 400);
        }
        $address = $request->json('address', null);
        if(empty($address)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Address must be specified.',
            ], 400);
        }

        $productIDs = [];
        foreach ($products as $product) {
            $productIDs[$product['id']] = $product['quantity'];
        }

        $messages = [];
        $price = 0;

        $order = Order::create([
            'user_id'       => $user['id'],
            'address'       => $address,
            'price'         => (float)$price,
            'status'        => 'waiting',
        ]);
        $id = $order->id;

        foreach ($productIDs as $productId => $quantity) {
            $product = Product::where('id', $productId)->first();
            if(empty($product)){
                $messages[] = '#' . $productId . ' products not found.';
                continue;
            }
            if($product->stock < $quantity){
                $messages[] = $product->name . '(' . $product->id . ') product is out of stock.';
                continue;
            }
            $price += ($quantity * $product->price);
            $product->stock -= $quantity;
            $product->save();
            OrderDetail::create([
                'order_id'      => $id,
                'product_id'    => $product->id,
                'quantity'      => $quantity
            ]);
        }

        $order->price = (float)$price;
        $order->save();
        $res = [
            'status'    => 1,
            'message'   => 'Order added.',
            'id'        => $order->id,
            'detailUrl' => route('detail', ['id' => $order->id]),
        ];
        if(!empty($messages)){
            $res['errors'] = $messages;
        }
        return response()->json($res, 200);
    }


    public function show($id)
    {
        $user = JWT::get();
        if($user === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $order = Order::where('user_id', $user['id'])
            ->where('id', $id)
            ->first();
        if(empty($order)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Order not found.',
            ], 404);
        }
        $res = [
            'id'            => $order->id,
            'address'       => $order->address,
            'price'         => $order->price,
            'status'        => $order->status,
            'created_at'    => $order->created_at,
        ];
        if(!empty($order->updated_at)){
            $res['updated_at'] = $order->updated_at;
        }
        $res['products'] = [];

        $details = OrderDetail::where('order_id', $id)->get();
        foreach ($details as $detail) {
            $product = $detail->product;
            $res['products'][] = [
                'id'            => $detail->product_id,
                'name'          => $product->name,
                'quantity'      => $detail->quantity,
            ];
        }

        return response()->json($res, 200);
    }

    public function update(Request $request, $id)
    {
        $user = JWT::get();
        if($user === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $order = Order::where('user_id', $user['id'])
            ->where('id', $id)
            ->first();
        if(empty($order)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Order not found.',
            ], 404);
        }
        if($order->status === 'shipped' || $order->status === 'delivered'){
            return response()->json([
                'status'    => 0,
                'message'   => 'A shipped or delivered order cannot be updated.',
            ], 400);
        }
        $products = $request->json('products');
        if(empty($products)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Products must be specified.',
            ], 400);
        }
        $address = $request->json('address', null);
        if(!empty($address)){
            $order->address = $address;
        }

        $productIDs = [];
        foreach ($products as $product) {
            $productIDs[$product['id']] = $product['quantity'];
        }

        $messages = [];

        $price = 0;

        $details = OrderDetail::where('order_id', $id)->get();
        foreach ($details as $detail) {
            $product = Product::where('id', $detail->product_id)->first();
            if(isset($productIDs[$detail->product_id])){
                if($productIDs[$detail->product_id] === $detail->quantity){
                    $price += ($product->price * $detail->quantity);
                    unset($productIDs[$detail->product_id]);
                    continue;
                }
                $diffQuantity = $detail->quantity - $productIDs[$detail->product_id];
                $detail->quantity = $productIDs[$detail->product_id];

                $product->stock += $diffQuantity;
                if($product->stock <= 0){
                    $messages[] = $product->name . '(' . $product->id . ') product is out of stock.';
                    continue;
                }
                $price += ($product->price * $productIDs[$detail->product_id]);
                $detail->save();
                $product->save();
                unset($productIDs[$detail->product_id]);
                continue;
            }
            OrderDetail::destroy($detail->id);
            unset($productIDs[$detail->product_id]);
        }

        foreach ($productIDs as $productId => $quantity) {
            $product = Product::where('id', $productId)->first();
            if(empty($product)){
                $messages[] = '#' . $productId . ' products not found.';
                continue;
            }
            if($product->stock < $quantity){
                $messages[] = $product->name . '(' . $product->id . ') product is out of stock.';
                continue;
            }
            $price += ($quantity * $product->price);
            $product->stock -= $quantity;
            $product->save();
            OrderDetail::create([
                'order_id'      => $id,
                'product_id'    => $product->id,
                'quantity'      => $quantity
            ]);
        }

        $order->price = (float)$price;
        $order->status = 'waiting';
        $order->save();
        $res = [
            'status'    => 1,
            'message'   => 'Order updated',
        ];
        if(!empty($messages)){
            $res['errors'] = $messages;
        }
        return response()->json($res, 200);
    }

    public function destroy($id)
    {
        $user = JWT::get();
        if($user === FALSE){
            return response()->json([
                'status'    => 0,
                'message'   => 'Authorization failed.',
            ], 401);
        }
        $order = Order::where('user_id', $user['id'])
                ->where('id', $id)
                ->first();
        if(empty($order)){
            return response()->json([
                'status'    => 0,
                'message'   => 'Order not found.',
            ], 400);
        }
        if($order->status === 'shipped'){
            return response()->json([
                'status'    => 0,
                'message'   => 'A shipped order cannot be deleted.',
            ], 400);
        }
        Order::destroy($id);
        OrderDetail::where('order_id', $id)->delete();
        return response()->json([
            'status'    => 1,
            'message'   => 'The order has been deleted.',
        ], 200);
    }

}
