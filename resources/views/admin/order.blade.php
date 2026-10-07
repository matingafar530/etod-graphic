<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>جزئیات {{ $order->order_number }} | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
    <header class="border-b border-[#ebe7f4] bg-white">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
            <div>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a>
                <h1 class="text-xl font-black">جزئیات سفارش</h1>
            </div>
            <a href="{{ route('admin.orders') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">بازگشت به سفارش‌ها</a>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
        @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>@endif
        <div class="grid gap-6 lg:grid-cols-[1fr_340px]">
            <section class="space-y-5">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-sm text-slate-500">شماره سفارش</p>
                            <h2 class="mt-1 text-2xl font-black text-violet-700">{{ $order->order_number }}</h2>
                        </div>
                        <span class="rounded-full bg-violet-50 px-4 py-2 text-sm font-bold text-violet-700">{{ $order->status }}</span>
                    </div>
                    <div class="mt-6 grid gap-4 border-t border-[#eeeaf5] pt-5 sm:grid-cols-2">
                        <div><p class="text-sm text-slate-500">نام مشتری</p><p class="mt-1 font-bold">{{ $order->customer_name }}</p></div>
                        <div><p class="text-sm text-slate-500">موبایل</p><p class="mt-1 font-bold">{{ $order->customer_phone }}</p></div>
                        <div class="sm:col-span-2"><p class="text-sm text-slate-500">آدرس</p><p class="mt-1 font-bold leading-7">{{ $order->customer_address }}</p></div>
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-xl font-black">آیتم‌های سفارش و مشخصات چاپ</h2>
                    <div class="mt-5 space-y-4">
                        @foreach($order->items as $item)
                            <article class="rounded-2xl border border-[#eeeaf5] p-4">
                                <div class="flex flex-wrap justify-between gap-3">
                                    <div>
                                        <h3 class="font-black">{{ $item->product_name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">SKU: {{ $item->sku ?: '—' }} | تعداد: {{ $item->quantity }}</p>
                                    </div>
                                    <strong class="text-violet-700">{{ number_format($item->total_price) }} ریال</strong>
                                </div>
                                <div class="mt-4 grid gap-3 rounded-xl bg-[#fbfaff] p-4 text-sm sm:grid-cols-2">
                                    <p><span class="text-slate-500">Variant:</span> {{ data_get($item->variant_snapshot, 'model') ?: 'عمومی' }}</p>
                                    <p><span class="text-slate-500">رنگ:</span> {{ data_get($item->variant_snapshot, 'color') ?: '—' }}</p>
                                    <p><span class="text-slate-500">سایز:</span> {{ data_get($item->variant_snapshot, 'size') ?: '—' }}</p>
                                    <p><span class="text-slate-500">قیمت چاپ:</span> {{ number_format($item->printing_price) }} ریال</p>
                                </div>
                                @if($item->customization_data)
                                    <details class="mt-3">
                                        <summary class="cursor-pointer text-sm font-bold text-violet-700">مشاهده داده Customizer</summary>
                                        <pre class="mt-3 overflow-auto rounded-xl bg-slate-950 p-3 text-xs text-green-300" dir="ltr">{{ json_encode($item->customization_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                    </details>
                                @endif
                                @if($item->preview_image_path)
                                    <img src="{{ route('orders.track.design', ['token' => $order->access_token, 'item' => $item]) }}" alt="طرح مشتری" class="mt-3 h-24 w-24 rounded-xl border border-[#eeeaf5] object-cover">
                                @endif
                                @if($order->portfolio_consent && $item->preview_image_path)
                                    <div class="mt-3 border-t border-[#eeeaf5] pt-3">
                                        @if($item->portfolioItem)
                                            <p class="text-xs font-bold text-green-700">در نمونه‌کارها موجود است{{ $item->portfolioItem->is_published ? ' (منتشرشده)' : ' (پیش‌نویس)' }} — <a href="{{ route('admin.portfolio') }}" class="text-violet-700">مدیریت</a></p>
                                        @else
                                            <details>
                                                <summary class="cursor-pointer text-sm font-bold text-violet-700">افزودن به نمونه‌کارها</summary>
                                                <form action="{{ route('admin.portfolio.store') }}" method="POST" class="mt-3 space-y-2 rounded-xl bg-[#fbfaff] p-3">
                                                    @csrf
                                                    <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                                                    <input name="title" maxlength="120" placeholder="عنوان (اختیاری — پیش‌فرض: نام محصول)" class="w-full rounded-xl border border-[#e5e0ef] px-3 py-2 text-sm">
                                                    <textarea name="description" rows="2" maxlength="1000" placeholder="توضیح (اختیاری)" class="w-full rounded-xl border border-[#e5e0ef] px-3 py-2 text-sm"></textarea>
                                                    <button type="submit" class="rounded-xl bg-violet-700 px-4 py-2 text-xs font-black text-white">ساخت پیش‌نویس نمونه‌کار</button>
                                                </form>
                                            </details>
                                        @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
            <aside class="space-y-5">
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black">تغییر وضعیت</h2>
                    <p class="mt-2 text-sm text-slate-500">فقط Transitionهای مجاز نمایش داده می‌شوند.</p>
                    @if($nextStatuses)
                        <form action="{{ route('admin.orders.status', $order) }}" method="POST" class="mt-5">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="w-full rounded-xl border border-[#e5e0ef] bg-white px-4 py-3 font-bold">
                                <option value="">انتخاب وضعیت جدید</option>
                                @foreach($nextStatuses as $status)
                                    <option value="{{ $status }}">{{ $status }}</option>
                                @endforeach
                            </select>
                            <button class="mt-3 w-full rounded-xl bg-violet-700 px-4 py-3 font-bold text-white">ثبت وضعیت</button>
                        </form>
                    @else
                        <p class="mt-5 rounded-xl bg-slate-50 p-4 text-sm font-bold text-slate-500">برای این وضعیت Transition دیگری مجاز نیست.</p>
                    @endif
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black">رضایت‌نامه انتشار</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-slate-500">نمونه‌کار فروشگاه</dt>
                            <dd class="font-bold {{ $order->portfolio_consent ? 'text-green-700' : 'text-slate-400' }}">{{ $order->portfolio_consent ? '✔ داده شده' : '✗ داده نشده' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-slate-500">شبکه‌های اجتماعی</dt>
                            <dd class="font-bold {{ $order->social_media_consent ? 'text-green-700' : 'text-slate-400' }}">{{ $order->social_media_consent ? '✔ داده شده' : '✗ داده نشده' }}</dd>
                        </div>
                    </dl>
                </div>
                <div class="rounded-3xl bg-white p-6 shadow-sm">
                    <h2 class="text-lg font-black">خلاصه مالی</h2>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex justify-between"><dt class="text-slate-500">جمع کالا</dt><dd class="font-bold">{{ number_format($order->subtotal) }} ریال</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">چاپ</dt><dd class="font-bold">{{ number_format($order->printing_total) }} ریال</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">ارسال</dt><dd class="font-bold">{{ number_format($order->shipping_price) }} ریال</dd></div>
                        <div class="flex justify-between border-t border-[#eeeaf5] pt-3"><dt class="font-black">مبلغ نهایی</dt><dd class="font-black text-violet-700">{{ number_format($order->total_price) }} ریال</dd></div>
                    </dl>
                </div>
            </aside>
        </div>
    </main>
</body>
</html>
