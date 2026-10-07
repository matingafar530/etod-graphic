<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->name }} | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbfaff] text-[#171326]">
    <header class="border-b border-[#eeeaf5] bg-white">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ route('home') }}" class="text-2xl font-black text-violet-700">Etod <span class="text-[#171326]">Graphic</span></a>
            <a href="{{ route('cart.index') }}" class="rounded-xl border border-[#ebe7f4] p-3 font-bold text-violet-700">سبد خرید</a>
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-5 py-10 lg:px-8">
        <a href="{{ route('home') }}" class="text-sm font-bold text-violet-700">← بازگشت به فروشگاه</a>
        <div class="mt-8 grid gap-8 lg:grid-cols-[.9fr_1.1fr]">
            <section class="rounded-3xl bg-white p-5 shadow-sm" data-customizer data-upload-url="{{ route('uploads.store') }}" data-csrf="{{ csrf_token() }}">
                <div class="relative mx-auto aspect-square max-w-xl overflow-hidden rounded-3xl bg-gradient-to-br from-violet-50 to-purple-100" data-design-stage>
                    <div class="absolute inset-[14%] rounded-2xl border-2 border-dashed border-violet-400/70 bg-white/30" data-print-area></div>
                    @if($product->images->first())
                        <img src="{{ route('products.images.show', $product->images->first()) }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" data-product-image class="absolute inset-0 h-full w-full object-contain p-8">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center text-8xl opacity-25">{{ $product->slug === 't-shirt' ? '👕' : '✦' }}</div>
                    @endif
                    <img data-design-image class="absolute hidden max-h-full max-w-full touch-none object-contain drop-shadow-lg" alt="پیش‌نمایش طرح">
                    <div data-crop-overlay class="absolute z-10 hidden cursor-crosshair touch-none bg-violet-900/10">
                        <div data-crop-rect class="absolute hidden border-2 border-dashed border-white shadow-[0_0_0_9999px_rgba(124,58,237,0.15)]"></div>
                    </div>
                </div>
                @if($product->images->count() > 1)
                    <div class="mt-4 flex flex-wrap justify-center gap-2" data-gallery>
                        @foreach($product->images as $image)
                            <button type="button" data-gallery-thumb="{{ route('products.images.show', $image) }}" aria-label="{{ $image->alt_text ?: $product->name }}"
                                class="overflow-hidden rounded-xl border-2 {{ $image->is($product->images->first()) ? 'border-violet-600' : 'border-transparent opacity-80 hover:opacity-100' }} transition">
                                <img src="{{ route('products.images.show', $image) }}" alt="{{ $image->alt_text ?: $product->name }}" class="h-16 w-16 object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
                <div class="hidden" data-print-area-wrap>
                    <label class="mt-5 block text-sm font-bold" for="design-scale">اندازه طرح</label>
                    <input id="design-scale" data-scale type="range" min="10" max="80" value="45" class="mt-3 w-full accent-violet-700">
                    <label class="mt-4 block text-sm font-bold" for="design-rotation">چرخش طرح</label>
                    <input id="design-rotation" data-rotation type="range" min="-180" max="180" step="5" value="0" class="mt-3 w-full accent-violet-700">
                    <button type="button" data-crop-toggle class="mt-5 w-full rounded-xl border border-[#e5e0ef] bg-white px-4 py-3 font-bold text-violet-700 transition hover:bg-violet-50">برش طرح</button>
                    <div data-crop-actions class="mt-3 hidden justify-center gap-2">
                        <button type="button" data-crop-apply class="rounded-xl bg-violet-700 px-4 py-2 font-bold text-white">اعمال برش</button>
                        <button type="button" data-crop-cancel class="rounded-xl border border-[#e5e0ef] bg-white px-4 py-2 font-bold text-slate-600">انصراف</button>
                    </div>
                </div>
                <label class="mt-5 flex cursor-pointer items-center justify-center rounded-2xl border-2 border-dashed border-violet-200 bg-violet-50 px-5 py-6 text-center font-bold text-violet-700">
                    <input data-image-input type="file" accept="image/jpeg,image/png,image/webp" class="hidden">
                    <span>تصویر خود را برای چاپ انتخاب کنید</span>
                </label>
                <p data-upload-status class="mt-3 text-center text-sm text-slate-500">فرمت‌های JPG، PNG و WEBP — حداکثر ۱۰ مگابایت</p>
            </section>

            <section>
                <p class="font-bold text-violet-700">{{ $product->category?->name }}</p>
                <h1 class="mt-2 text-4xl font-black">{{ $product->name }}</h1>
                <p class="mt-4 leading-8 text-slate-600">{{ $product->description ?: 'این محصول با طرح دلخواه شما چاپ می‌شود.' }}</p>

                <form data-cart-form action="{{ route('cart.items.store') }}" method="POST" class="mt-8 rounded-3xl bg-white p-6 shadow-sm">
                    @csrf
                    <input type="hidden" name="upload_id" data-upload-id value="">
                    <input type="hidden" name="customization_data" data-customization-data value="{}">
                    <label class="block text-sm font-bold" for="variant-id">انتخاب رنگ، سایز یا مدل</label>
                    <select id="variant-id" name="variant_id" required class="mt-3 w-full rounded-xl border border-[#e5e0ef] bg-white px-4 py-3 font-bold">
                        <option value="">یک گزینه را انتخاب کنید</option>
                        @foreach($product->variants as $variant)
                            <option value="{{ $variant->id }}" @disabled(!$variant->is_active || $variant->stock <= $variant->reserved_stock)>
                                {{ $variant->color?->name ?: 'رنگ عمومی' }} / {{ $variant->size?->name ?: $variant->model ?: 'مدل عمومی' }} — موجودی: {{ max(0, $variant->stock - $variant->reserved_stock) }}
                            </option>
                        @endforeach
                    </select>
                    <label class="mt-5 block text-sm font-bold" for="quantity">تعداد</label>
                    <input id="quantity" name="quantity" type="number" min="1" max="50" value="1" required class="mt-3 w-full rounded-xl border border-[#e5e0ef] px-4 py-3 font-bold">
                    @if($errors->any())<div class="mt-4 rounded-xl bg-red-50 p-4 text-sm font-bold text-red-700">{{ $errors->first() }}</div>@endif
                    <button type="submit" class="mt-6 w-full rounded-2xl bg-violet-700 px-5 py-4 font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-800">افزودن به سبد خرید</button>
                    <p class="mt-3 text-center text-xs text-slate-500">قیمت و موجودی در سمت سرور دوباره بررسی می‌شود.</p>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
