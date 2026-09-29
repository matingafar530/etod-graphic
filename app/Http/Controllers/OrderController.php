<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $owned = $order->user_id !== null && $order->user_id === $request->user()?->id;
        $sessionOwned = $order->user_id === null && $order->session_id === $request->session()->getId();
        abort_unless($owned || $sessionOwned, 404);

        return view('storefront.order', ['order' => $order->load('items', 'payments')]);
    }
}
