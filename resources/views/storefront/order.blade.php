<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سفارش {{ $order->order_number }} | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#fbfaff] text-[#171326]">
    <header class="border-b border-[#eeeaf5] bg-white">
        <div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8">
            <a href="{{ route('home') }}" class="text-2xl font-black text-violet-700">Etod <span class="text-[#171326]">Graphic</span></a>
            <a href="{{ route('home') }}" class="font-bold text-violet-700">فروشگاه</a>
        </div>
    </header>

    @php
        $steps = [
            'pending_payment' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شد',
            'processing' => 'در حال پردازش',
            'printing' => 'در حال چاپ',
            'ready' => 'آماده ارسال',
            'shipped' => 'ارسال شد',
            'completed' => 'تکمیل شد',
        ];
        $failed = in_array($order->status, ['cancelled', 'payment_failed'], true);
        $currentStep = array_search($order->status, array_keys($steps), true);
        $trackUrl = $trackToken ? route('orders.track', ['token' => $trackToken]) : null;
    @endphp

    <main class="mx-auto max-w-4xl px-5 py-10 lg:px-8">
        @if(session('success'))
            <div class="rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-3 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="mt-3 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ $errors->first() }}</div>
        @endif

        <section class="mt-6 rounded-3xl bg-white p-8 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-100 text-3xl text-green-600">✓</div>
            <h1 class="mt-4 text-3xl font-black">{{ $failed ? 'سفارش شما' : 'سفارش شما ثبت شد' }}</h1>
            <p class="mt-3 text-sm text-slate-500">شماره سفارش</p>
            <p class="text-2xl font-black text-violet-700">{{ $order->order_number }}</p>
            @if($trackUrl)
                <div class="mt-6 rounded-2xl bg-violet-50 p-4 text-right">
                    <p class="font-bold text-violet-700">لینک اختصاصی پیگیری سفارش</p>
                    <a href="{{ $trackUrl }}" class="mt-1 block break-all text-sm text-violet-700 underline">{{ $trackUrl }}</a>
                    <p class="mt-2 text-xs text-slate-500">این لینک را ذخیره کنید؛ با آن حتی در مرورگر دیگر می‌توانید وضعیت سفارش و طرح‌های خود را ببینید.</p>
                </div>
            @endif
        </section>

        <section class="mt-6 rounded-3xl bg-white p-6 shadow-sm">
            @if($failed)
                <div class="rounded-2xl bg-red-50 p-4 font-bold text-red-700">وضعیت فعلی سفارش: {{ $order->status === 'cancelled' ? 'لغو شده' : 'پرداخت ناموفق' }}</div>
            @else
                <h2 class="font-black">وضعیت سفارش</h2>
                <ol class="mt-5 grid grid-cols-3 gap-y-6 sm:grid-cols-7">
                    @foreach($steps as $key => $label)
                        @php $done = $currentStep !== false && $key === $order->status; $passed = $currentStep !== false && array_search($key, array_keys($steps), true) < $currentStep; @endphp
                        <li class="flex flex-col items-center gap-2 text-center">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-black {{ $done ? 'bg-violet-700 text-white' : ($passed ? 'bg-violet-200 text-violet-800' : 'bg-slate-100 text-slate-400') }}">{{ $passed ? '✓' : ($done ? '●' : '') }}</span>
                            <span class="text-[11px] font-bold leading-4 {{ $done || $passed ? 'text-violet-700' : 'text-slate-400' }}">{{ $label }}</span>
                        </li>
                    @endforeach
                </ol>
            @endif

            <div class="mt-6 grid gap-4 border-t border-[#eeeaf5] pt-6 sm:grid-cols-3">
                <div>
                    <span class="text-sm text-slate-500">وضعیت پرداخت</span>
                    <strong class="mt-1 block">{{ $order->payment_status === 'paid' ? 'پرداخت آزمایشی موفق' : 'در انتظار پرداخت' }}</strong>
                </div>
                <div>
                    <span class="text-sm text-slate-500">مبلغ کل</span>
                    <strong class="mt-1 block text-violet-700">{{ number_format($order->total_price) }} ریال</strong>
                </div>
                <div>
                    <span class="text-sm text-slate-500">تاریخ ثبت</span>
                    <strong class="mt-1 block">{{ \Illuminate\Support\Carbon::parse($order->created_at)->locale('fa')->isoFormat('YYYY/MM/DD — HH:mm') }}</strong>
                </div>
            </div>

            @if($order->payment_status !== 'paid')
                <form action="{{ route('orders.mock-payment', $order) }}" method="POST" class="mt-6 border-t border-[#eeeaf5] pt-6">
                    @csrf
                    @if($trackToken)<input type="hidden" name="token" value="{{ $trackToken }}">@endif
                    <button type="submit" class="rounded-2xl bg-violet-700 px-6 py-4 font-black text-white shadow-lg shadow-violet-200 transition hover:bg-violet-800">پرداخت آزمایشی (Mock)</button>
                    <p class="mt-3 text-sm text-slate-500">این دکمه فقط برای تست داخلی است و به بانک یا زرین‌پال متصل نیست.</p>
                </form>
            @else
                <div class="mt-6 rounded-2xl bg-violet-50 p-4 font-bold text-violet-700">پرداخت ثبت شده و سفارش آماده پردازش است. به‌محض شروع چاپ، وضعیت همین صفحه به‌روز می‌شود.</div>
            @endif
        </section>

        <h2 class="mt-8 font-black">اقلام سفارش</h2>
        <section class="mt-3 space-y-3">
            @foreach($order->items as $item)
                <article class="flex flex-col gap-4 rounded-2xl bg-white p-5 shadow-sm sm:flex-row sm:items-center">
                    @if($item->preview_image_path && $trackToken)
                        <img src="{{ route('orders.track.design', ['token' => $trackToken, 'item' => $item]) }}" alt="طرح سفارشی" class="h-24 w-24 shrink-0 rounded-xl border border-[#eeeaf5] object-cover">
                    @endif
                    <div class="flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <strong>{{ $item->product_name }}</strong>
                            <span class="text-sm font-bold text-slate-500">{{ $item->quantity }} عدد</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">
                            {{ $item->variant_snapshot['color'] ?? '' }} {{ $item->variant_snapshot['size'] ?? $item->variant_snapshot['model'] ?? '' }} — کد: {{ $item->sku }}
                        </p>
                        <p class="mt-1 text-sm font-bold text-violet-700">{{ number_format($item->total_price) }} ریال</p>
                    </div>
                </article>
                @if($order->status === 'completed' && $trackToken)
                    <div class="mt-3 border-t border-[#eeeaf5] pt-4">
                        @if($item->review)
                            <p class="text-sm font-bold text-green-700">نظر شما برای این قلم ثبت شده{{ $item->review->status === 'approved' ? ' و منتشر شده است.' : ' و در انتظار تأیید مدیر است.' }}</p>
                        @elseif($item->variant?->product_id)
                            <form action="{{ route('reviews.store', ['token' => $trackToken, 'item' => $item]) }}" method="POST" class="rounded-2xl bg-[#fbfaff] p-4">
                                @csrf
                                <p class="text-sm font-black">نظر شما درباره این محصول</p>
                                <div class="mt-3 flex flex-wrap items-center gap-4">
                                    <label class="flex items-center gap-2 text-sm font-bold">امتیاز
                                        <select name="rating" required class="rounded-xl border border-[#e5e0ef] bg-white px-3 py-2 font-bold">
                                            <option value="5">۵ ★</option>
                                            <option value="4">۴ ★</option>
                                            <option value="3">۳ ★</option>
                                            <option value="2">۲ ★</option>
                                            <option value="1">۱ ★</option>
                                        </select>
                                    </label>
                                    <label class="flex flex-1 items-center gap-2 text-sm font-bold">نام شما
                                        <input name="author_name" value="{{ old('author_name', $order->customer_name) }}" maxlength="80" required class="w-full rounded-xl border border-[#e5e0ef] px-3 py-2">
                                    </label>
                                </div>
                                <textarea name="body" rows="3" maxlength="2000" placeholder="تجربه خود از کیفیت چاپ و محصول را بنویسید (اختیاری)" class="mt-3 w-full rounded-xl border border-[#e5e0ef] px-3 py-2 text-sm">{{ old('body') }}</textarea>
                                <button type="submit" class="mt-3 rounded-xl bg-violet-700 px-5 py-2 text-sm font-black text-white transition hover:bg-violet-800">ثبت نظر</button>
                            </form>
                        @endif
                    </div>
                @endif
            @endforeach
        </section>

        <section class="mt-6 rounded-3xl bg-white p-6 text-sm shadow-sm">
            <h2 class="font-black">اطلاعات ارسال</h2>
            <p class="mt-2 text-slate-600">{{ $order->customer_name }} — {{ $order->customer_phone }}</p>
            <p class="mt-1 leading-7 text-slate-600">{{ $order->customer_address }}</p>
        </section>

        <div class="mt-8 flex flex-wrap justify-between gap-3">
            <a href="{{ route('home') }}" class="rounded-2xl border border-[#ebe7f4] bg-white px-6 py-3 font-bold text-violet-700">ادامه خرید</a>
            <button type="button" onclick="window.print()" class="rounded-2xl border border-[#ebe7f4] bg-white px-6 py-3 font-bold text-slate-600">چاپ رسید</button>
        </div>
    </main>
</body>
</html>
