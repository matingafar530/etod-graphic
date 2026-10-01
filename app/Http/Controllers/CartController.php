<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): View
    {
        $cart = $this->cartService->currentCart($request)->load('items.variant.product', 'items.variant.color', 'items.variant.size', 'items.upload');

        return view('storefront.cart', ['cart' => $cart, 'totals' => $this->cartService->totals($cart)]);
    }

    public function store(AddToCartRequest $request): RedirectResponse
    {
        $customization = $request->input('customization_data');
        $customization = is_array($customization) ? $customization : [];

        if ($request->filled('upload_id')) {
            $customization['upload_id'] = $request->integer('upload_id');
        }

        $this->cartService->add($request, ProductVariant::findOrFail($request->integer('variant_id')), $request->integer('quantity'), $customization);

        return redirect()->route('cart.index')->with('success', 'محصول با موفقیت به سبد خرید اضافه شد.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($item->cart_id === $this->cartService->currentCart($request)->id, 404);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:50']]);
        $item->update($data);

        return back()->with('success', 'تعداد محصول به‌روزرسانی شد.');
    }

    public function destroy(Request $request, CartItem $item): RedirectResponse
    {
        abort_unless($item->cart_id === $this->cartService->currentCart($request)->id, 404);
        $item->delete();

        return back()->with('success', 'محصول از سبد خرید حذف شد.');
    }
}
