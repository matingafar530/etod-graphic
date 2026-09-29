<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سبد خرید | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbfaff] text-[#171326]">
    <header class="border-b border-[#eeeaf5] bg-white">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ route('home') }}" class="text-2xl font-black text-violet-700">Etod <span class="text-[#171326]">Graphic</span></a>
            <a href="{{ route('home') }}" class="font-bold text-violet-700">ادامه خرید</a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-5 py-10 lg:px-8">
        <h1 class="text-4xl font-black">سبد خرید</h1>

        @if(session('success'))
            <div class="mt-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>
        @endif

        @if($cart->items->isEmpty())
            <div class="mt-10 rounded-3xl bg-white p-12 text-center shadow-sm">
                <div class="text-6xl">🛒</div>
                <h2 class="mt-5 text-2xl font-black">سبد خرید شما خالی است</h2>
                <a href="{{ route('home') }}#products" class="mt-6 inline-block rounded-2xl bg-violet-700 px-6 py-3 font-bold text-white">مشاهده محصولات</a>
            </div>
        @else
            <div class="mt-8 grid gap-6 lg:grid-cols-[1fr_340px]">
                <section class="space-y-4">
                    @foreach($cart->items as $item)
                        <article class="flex gap-4 rounded-3xl bg-white p-5 shadow-sm">
                            <div class="flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-violet-50 text-4xl">
                                @if($item->upload)
                                    <img src="{{ route('uploads.show', $item->upload) }}" class="h-full w-full object-contain" alt="پیش‌نمایش طرح">
                                @else
                                    ✦
                                @endif
                            </div>
                            <div class="flex-1">
                                <h2 class="font-black">{{ $item->variant->product->name }}</h2>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $item->variant->color?->name }}
                                    @if($item->variant->size) / {{ $item->variant->size->name }} @endif
                                </p>
                                <p class="mt-3 font-bold text-violet-700">{{ number_format($item->unit_price) }} ریال</p>
                            </div>
                            <div class="flex flex-col items-end justify-between">
                                <form action="{{ route('cart.items.update', $item) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input name="quantity" type="number" min="1" max="50" value="{{ $item->quantity }}" onchange="this.form.submit()" class="w-20 rounded-xl border border-[#e8e3f1] px-3 py-2 text-center">
                                </form>
                                <form action="{{ route('cart.items.destroy', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm font-bold text-red-500">حذف</button>
                                </form>
                            </div>
                        </article>
                    @endforeach
                </section>

                <aside class="h-fit rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black">خلاصه سفارش</h2>
                    <div class="mt-6 flex justify-between text-slate-600">
                        <span>جمع کل</span>
                        <strong class="text-xl text-violet-700">{{ number_format($totals['total']) }} ریال</strong>
                    </div>
                    <button disabled class="mt-7 w-full cursor-not-allowed rounded-2xl bg-slate-200 px-5 py-4 font-black text-slate-500">ادامه به پرداخت — به‌زودی</button>
                </aside>
            </div>
        @endif
    </main>
</body>
</html>
