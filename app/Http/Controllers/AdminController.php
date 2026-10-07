<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryRequest;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\PrintJob;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Services\InventoryService;
use App\Services\OrderStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function printQueue(Request $request): View
    {
        $status = $request->query('status');
        abort_if($status && ! in_array($status, ['queued', 'printing', 'failed', 'completed'], true), 422);
        $jobs = PrintJob::query()->with('order', 'orderItem', 'variant.product', 'variant.color', 'variant.size')->when($status, fn ($query) => $query->where('status', $status), fn ($query) => $query->whereIn('status', ['queued', 'printing', 'failed']))->latest()->paginate(30)->withQueryString();

        return view('admin.print-queue', compact('jobs', 'status'));
    }

    public function inventory(Request $request): View
    {
        $lowStock = $request->boolean('low_stock');
        $threshold = max(0, min(100000, $request->integer('threshold', 3)));
        $variants = ProductVariant::query()->with('product', 'color', 'size')->when($lowStock, fn ($query) => $query->whereRaw('(stock - reserved_stock) <= ?', [$threshold]))->orderBy('stock')->paginate(30)->withQueryString();

        return view('admin.inventory', compact('variants', 'lowStock', 'threshold'));
    }

    public function inventoryMovements(Request $request): View
    {
        $variantId = $request->integer('variant_id') ?: null;
        $movements = InventoryMovement::query()->with('variant.product', 'variant.color', 'variant.size')->when($variantId, fn ($query) => $query->where('product_variant_id', $variantId))->latest()->paginate(40)->withQueryString();

        return view('admin.inventory-movements', ['movements' => $movements, 'variants' => ProductVariant::with('product')->orderBy('sku')->get(), 'variantId' => $variantId]);
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

    public function confirmArchiveProduct(Product $product): View
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        return view('admin.product-archive', compact('product'));
    }

    public function archiveProduct(Request $request, Product $product): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:500']]);
        $old = ['status' => $product->status];
        $product->update(['status' => 'archived']);
        DB::table('audit_logs')->insert(['user_id' => $request->user()?->id, 'action' => 'product.archived', 'auditable_type' => Product::class, 'auditable_id' => $product->id, 'old_values' => json_encode($old), 'new_values' => json_encode(['status' => 'archived', 'reason' => $data['reason']], JSON_UNESCAPED_UNICODE), 'ip_address' => $request->ip(), 'created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('admin.products')->with('success', 'محصول بایگانی شد؛ اطلاعات و سفارش‌های قبلی حفظ شده‌اند.');
    }

    public function reviews(Request $request): View
    {
        $status = $request->query('status');
        abort_if($status !== null && ! array_key_exists($status, Review::MODERATION_TRANSITIONS), 422);
        $reviews = Review::query()->with('product', 'orderItem.order')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reviews', ['reviews' => $reviews, 'status' => $status]);
    }

    public function updateReviewStatus(Request $request, Review $review): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);
        $data = $request->validate(['status' => ['required', 'in:approved,rejected,hidden']]);
        if (! $review->canTransitionTo($data['status'])) {
            return back()->with('error', "تغییر وضعیت نظر از «{$review->status}» به «{$data['status']}» مجاز نیست.");
        }

        $old = ['status' => $review->status];
        $review->update(['status' => $data['status'], 'reviewed_at' => now()]);
        DB::table('audit_logs')->insert(['user_id' => $request->user()?->id, 'action' => 'review.moderated', 'auditable_type' => Review::class, 'auditable_id' => $review->id, 'old_values' => json_encode($old), 'new_values' => json_encode(['status' => $data['status']], JSON_UNESCAPED_UNICODE), 'ip_address' => $request->ip(), 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'وضعیت نظر به‌روزرسانی شد.');
    }
}
