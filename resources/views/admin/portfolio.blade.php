<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>نمونه‌کارها | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
    <header class="border-b border-[#ebe7f4] bg-white"><div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a><h1 class="text-xl font-black">نمونه‌کارها</h1></div><div class="flex items-center gap-3"><a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">داشبورد</a><a href="{{ route('home') }}" class="rounded-xl bg-violet-700 px-4 py-2 text-sm font-bold text-white">فروشگاه</a></div></div></header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
        <div class="mb-5 rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">پنل فعلی فقط برای محیط توسعه است و هنوز احراز هویت ادمین ندارد. نمونه‌کار جدید از صفحه جزئیات سفارش ساخته می‌شود.</div>
        @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>@endif
        <section class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($items as $item)
                <article class="overflow-hidden rounded-3xl bg-white shadow-sm">
                    <img src="{{ route('admin.portfolio.image', $item) }}" alt="{{ $item->title }}" class="h-44 w-full bg-[#fbfaff] object-contain p-3">
                    <div class="p-5">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-sm font-black">{{ $item->title }}</h2>
                            <span class="rounded-full px-3 py-1 text-xs font-bold {{ $item->is_published ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">{{ $item->is_published ? 'منتشرشده' : 'پیش‌نویس' }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ $item->product?->name ?? '—' }} — سفارش {{ $item->order?->order_number ?? '—' }}</p>
                        <div class="mt-4 flex gap-2">
                            <form action="{{ route('admin.portfolio.publish', $item) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $item->is_published ? 'bg-slate-200 text-slate-700' : 'bg-green-600 text-white' }}">{{ $item->is_published ? 'لغو انتشار' : 'انتشار' }}</button>
                            </form>
                            <form action="{{ route('admin.portfolio.destroy', $item) }}" method="POST" onsubmit="return confirm('این نمونه‌کار حذف شود؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg bg-red-50 px-3 py-1.5 text-xs font-bold text-red-700">حذف</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-3xl bg-white p-16 text-center text-slate-500 shadow-sm">هنوز نمونه‌کاری ساخته نشده است.</p>
            @endforelse
        </section>
        @if($items->hasPages())<div class="mt-6">{{ $items->links() }}</div>@endif
    </main>
</body>
</html>
