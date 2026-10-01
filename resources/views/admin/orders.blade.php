<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>سفارش‌ها | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
    <header class="border-b border-[#ebe7f4] bg-white"><div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a><h1 class="text-xl font-black">مدیریت سفارش‌ها</h1></div><div class="flex items-center gap-3"><a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">داشبورد</a><a href="{{ route('home') }}" class="rounded-xl bg-violet-700 px-4 py-2 text-sm font-bold text-white">فروشگاه</a></div></div></header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
        <div class="mb-5 rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">پنل فعلی فقط برای محیط توسعه است و هنوز احراز هویت ادمین ندارد.</div>
        @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif
        <section class="overflow-hidden rounded-3xl bg-white shadow-sm"><div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#eeeaf5] p-6"><h2 class="text-xl font-black">همه سفارش‌ها</h2><span class="text-sm text-slate-500">{{ $orders->total() }} سفارش</span></div><div class="overflow-x-auto"><table class="w-full min-w-[820px] text-right text-sm"><thead class="bg-[#fbfaff] text-slate-500"><tr><th class="px-5 py-4">شماره سفارش</th><th class="px-5 py-4">مشتری</th><th class="px-5 py-4">موبایل</th><th class="px-5 py-4">وضعیت</th><th class="px-5 py-4">پرداخت</th><th class="px-5 py-4">مبلغ</th><th class="px-5 py-4"></th></tr></thead><tbody>@forelse($orders as $order)<tr class="border-t border-[#f0edf6]"><td class="px-5 py-4 font-black text-violet-700">{{ $order->order_number }}</td><td class="px-5 py-4">{{ $order->customer_name }}</td><td class="px-5 py-4">{{ $order->customer_phone }}</td><td class="px-5 py-4"><span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-bold text-violet-700">{{ $order->status }}</span></td><td class="px-5 py-4">{{ $order->payment_status }}</td><td class="px-5 py-4 font-bold">{{ number_format($order->total_price) }} ریال</td><td class="px-5 py-4"><a href="{{ route('admin.orders.show', $order) }}" class="font-bold text-violet-700 hover:text-violet-900">مشاهده</a></td></tr>@empty<tr><td colspan="7" class="px-5 py-16 text-center text-slate-500">هنوز سفارشی ثبت نشده است.</td></tr>@endforelse</tbody></table></div>@if($orders->hasPages())<div class="border-t border-[#eeeaf5] p-5">{{ $orders->links() }}</div>@endif</section>
    </main>
</body>
</html>
