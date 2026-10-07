<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('storefront.home', [
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'products' => Product::query()->with('category')->where('status', 'published')->latest()->get(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->status === 'published', 404);

        return view('storefront.product', [
            'product' => $product->load(['images', 'variants' => fn ($query) => $query->where('is_active', true)->with(['color', 'size', 'printTemplate'])]),
            'reviews' => $product->reviews()->approved()->orderByDesc('reviewed_at')->limit(6)->get(['rating', 'body', 'author_name', 'reviewed_at', 'created_at']),
        ]);
    }
}
