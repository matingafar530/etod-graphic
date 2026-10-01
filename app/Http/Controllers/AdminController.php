<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\PrintJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\InventoryService;
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

    public function printQueue(): View
    {
        return view('admin.print-queue', ['jobs' => PrintJob::query()->with('order', 'orderItem', 'variant.product', 'variant.color', 'variant.size')->whereIn('status', ['queued', 'printing'])->latest()->paginate(30)]);
    }

    public function inventory(): View
    {
        return view('admin.inventory', ['variants' => ProductVariant::query()->with('product', 'color', 'size')->orderBy('stock')->paginate(30)]);
    }

    public function adjustInventory(AdjustInventoryRequest $request, ProductVariant $variant, InventoryService $inventory): RedirectResponse
    {
        $inventory->adjust($variant, $request->integer('quantity'), $request->string('reason')->toString());

        return back()->with('success', 'موجودی با موفقیت به‌روزرسانی شد.');
    }

    public function products(): View
    {
        return view('admin.products', ['products' => Product::query()->with('category')->withCount('variants', 'images')->latest()->paginate(20)]);
    }

    public function createProduct(): View
    {
        return view('admin.product-form', ['product' => new Product(['status' => 'draft', 'is_customizable' => true]), 'categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }

    public function storeProduct(ProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()->route('admin.products.edit', $product)->with('success', 'محصول ایجاد شد.');
    }

    public function editProduct(Product $product): View
    {
        return view('admin.product-form', ['product' => $product->load('images', 'variants'), 'categories' => Category::where('is_active', true)->orderBy('name')->get()]);
    }

    public function updateProduct(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return back()->with('success', 'محصول به‌روزرسانی شد.');
    }
}
