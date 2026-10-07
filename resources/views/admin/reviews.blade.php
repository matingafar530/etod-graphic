<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مدیریت نظرات | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
    <header class="border-b border-[#ebe7f4] bg-white"><div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a><h1 class="text-xl font-black">مدیریت نظرات</h1></div><div class="flex items-center gap-3"><a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">داشبورد</a><a href="{{ route('home') }}" class="rounded-xl bg-violet-700 px-4 py-2 text-sm font-bold text-white">فروشگاه</a></div></div></header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
        <div class="mb-5 rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">پنل فعلی فقط برای محیط توسعه است و هنوز احراز هویت ادمین ندارد.</div>
        @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="mb-5 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ session('error') }}</div>@endif
        <div class="mb-5 flex flex-wrap gap-2">
            @foreach(['pending' => 'در انتظار تأیید', 'approved' => 'منتشرشده', 'rejected' => 'ردشده', 'hidden' => 'مخفی', null => 'همه'] as $key => $label)
                <a href="{{ route('admin.reviews', $key ? ['status' => $key] : []) }}" class="rounded-xl px-4 py-2 text-sm font-bold {{ ($status ?? null) === $key ? 'bg-violet-700 text-white' : 'border border-[#e8e3f1] bg-white text-slate-600' }}">{{ $label }}</a>
            @endforeach
        </div>
        <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-right text-sm">
                    <thead class="bg-[#fbfaff] text-slate-500">
                        <tr><th class="px-5 py-4">محصول</th><th class="px-5 py-4">نویسنده</th><th class="px-5 py-4">امتیاز</th><th class="px-5 py-4">متن نظر</th><th class="px-5 py-4">سفارش</th><th class="px-5 py-4">وضعیت</th><th class="px-5 py-4">اقدام</th></tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                            <tr class="border-t border-[#f0edf6]">
                                <td class="px-5 py-4 font-bold">{{ $review->product?->name ?? '—' }}</td>
                                <td class="px-5 py-4">{{ $review->author_name ?? ($review->user?->name ?? '—') }}</td>
                                <td class="px-5 py-4 text-amber-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</td>
                                <td class="max-w-[280px] px-5 py-4 leading-6 text-slate-600">{{ $review->body ?: '—' }}</td>
                                <td class="px-5 py-4 text-xs text-slate-500">{{ $review->orderItem?->order?->order_number ?? '—' }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ ['pending' => 'bg-amber-50 text-amber-700', 'approved' => 'bg-green-50 text-green-700', 'rejected' => 'bg-red-50 text-red-700', 'hidden' => 'bg-slate-100 text-slate-500'][$review->status] }}">{{ ['pending' => 'در انتظار تأیید', 'approved' => 'منتشرشده', 'rejected' => 'ردشده', 'hidden' => 'مخفی'][$review->status] }}</span></td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(\App\Models\Review::MODERATION_TRANSITIONS[$review->status] ?? [] as $next)
                                            <form action="{{ route('admin.reviews.status', $review) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ $next }}">
                                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-bold {{ $next === 'approved' ? 'bg-green-600 text-white' : ($next === 'rejected' ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-700') }}">{{ ['approved' => 'تأیید', 'rejected' => 'رد', 'hidden' => 'مخفی'][$next] }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-16 text-center text-slate-500">نظری با این وضعیت وجود ندارد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reviews->hasPages())<div class="border-t border-[#eeeaf5] p-5">{{ $reviews->links() }}</div>@endif
        </section>
    </main>
</body>
</html>
