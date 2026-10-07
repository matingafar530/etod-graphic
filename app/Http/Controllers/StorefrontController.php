<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\PortfolioItem;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class StorefrontController extends Controller
{
    public function index(): View
    {
        return view('storefront.home', [
            'categories' => Category::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'products' => Product::query()->with('category')->where('status', 'published')->latest()->get(),
            'portfolioItems' => PortfolioItem::query()->published()->with('product')->orderByDesc('published_at')->limit(8)->get(),
        ]);
    }

    public function portfolioImage(PortfolioItem $portfolio): BinaryFileResponse
    {
        abort_unless($portfolio->is_published, 404);
        abort_unless(Storage::disk('local')->exists($portfolio->image_path), 404);

        return response()->file(Storage::disk('local')->path($portfolio->image_path), ['Cache-Control' => 'public, max-age=86400']);
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
