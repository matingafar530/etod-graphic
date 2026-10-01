<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'counts' => [
                'new' => Order::whereIn('status', ['pending_payment', 'paid'])->count(),
                'processing' => Order::where('status', 'processing')->count(),
                'printing' => Order::where('status', 'printing')->count(),
                'ready' => Order::where('status', 'ready')->count(),
                'shipping' => Order::where('status', 'shipped')->count(),
                'completed' => Order::where('status', 'completed')->count(),
            ],
            'recentOrders' => Order::query()->latest()->limit(10)->get(),
        ]);
    }

    public function orders(): View
    {
        return view('admin.orders', ['orders' => Order::query()->latest()->paginate(20)]);
    }

    public function show(Order $order, OrderStatusService $statuses): View
    {
        return view('admin.order', ['order' => $order->load('items', 'payments'), 'nextStatuses' => $statuses->allowedNextStatuses($order->status)]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, OrderStatusService $statuses): RedirectResponse
    {
        $statuses->change($order, $request->string('status')->toString());

        return back()->with('success', 'وضعیت سفارش با موفقیت تغییر کرد.');
    }
}
