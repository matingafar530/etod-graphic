<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>اعلان‌ها و صف‌ها | Etod Graphic</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
    <header class="border-b border-[#ebe7f4] bg-white"><div class="mx-auto flex h-20 max-w-7xl items-center justify-between px-5 lg:px-8"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a><h1 class="text-xl font-black">اعلان‌ها و صف‌ها</h1></div><div class="flex items-center gap-3"><a href="{{ route('admin.dashboard') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">داشبورد</a><a href="{{ route('home') }}" class="rounded-xl bg-violet-700 px-4 py-2 text-sm font-bold text-white">فروشگاه</a></div></div></header>
    <main class="mx-auto max-w-7xl px-5 py-8 lg:px-8">
        <div class="mb-5 rounded-2xl bg-amber-50 p-4 text-sm font-bold text-amber-800">درایور فعلی اعلان‌ها Log است؛ پیام‌ها به‌جای ارسال واقعی ثبت می‌شوند. اتصال SMS.ir و واتساپ طبق تصمیم مالک موکول شده است.</div>
        @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif

        <section class="overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#eeeaf5] p-6"><h2 class="text-xl font-black">کارهای شکسته صف</h2><span class="text-sm text-slate-500">{{ $failedJobs->count() }} مورد</span></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-right text-sm">
                    <thead class="bg-[#fbfaff] text-slate-500"><tr><th class="px-5 py-4">شناسه</th><th class="px-5 py-4">صف</th><th class="px-5 py-4">خطا</th><th class="px-5 py-4">زمان</th><th class="px-5 py-4"></th></tr></thead>
                    <tbody>
                        @forelse($failedJobs as $job)
                            <tr class="border-t border-[#f0edf6]">
                                <td class="px-5 py-4 text-xs" dir="ltr">{{ \Illuminate\Support\Str::limit($job->uuid, 12) }}</td>
                                <td class="px-5 py-4">{{ $job->queue }}</td>
                                <td class="max-w-[320px] px-5 py-4 text-xs text-red-700" dir="ltr">{{ \Illuminate\Support\Str::limit($job->exception, 120) }}</td>
                                <td class="px-5 py-4 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($job->failed_at)->locale('fa')->isoFormat('YYYY/MM/DD HH:mm') }}</td>
                                <td class="px-5 py-4">
                                    <form action="{{ route('admin.failed-jobs.retry', $job->uuid) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-violet-700 px-3 py-1.5 text-xs font-bold text-white">تلاش مجدد</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">کار شکسته‌ای وجود ندارد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="mt-8 overflow-hidden rounded-3xl bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#eeeaf5] p-6"><h2 class="text-xl font-black">لاگ اعلان‌ها</h2><span class="text-sm text-slate-500">{{ $logs->total() }} رکورد</span></div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-right text-sm">
                    <thead class="bg-[#fbfaff] text-slate-500"><tr><th class="px-5 py-4">کانال</th><th class="px-5 py-4">رویداد</th><th class="px-5 py-4">سفارش</th><th class="px-5 py-4">گیرنده</th><th class="px-5 py-4">پیام</th><th class="px-5 py-4">وضعیت</th><th class="px-5 py-4">زمان</th></tr></thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr class="border-t border-[#f0edf6]">
                                <td class="px-5 py-4 font-bold">{{ $log->channel }}</td>
                                <td class="px-5 py-4 text-xs" dir="ltr">{{ $log->event }}</td>
                                <td class="px-5 py-4">{{ $log->order_id ? '#'.$log->order_id : '—' }}</td>
                                <td class="px-5 py-4" dir="ltr">{{ $log->recipient ?? '—' }}</td>
                                <td class="max-w-[320px] px-5 py-4 leading-6 text-slate-600">{{ $log->message }}</td>
                                <td class="px-5 py-4"><span class="rounded-full px-3 py-1 text-xs font-bold {{ $log->status === 'sent' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">{{ $log->status === 'sent' ? 'ارسال‌شده' : 'ناموفق' }}</span>@if($log->error)<p class="mt-1 text-xs text-red-600" dir="ltr">{{ \Illuminate\Support\Str::limit($log->error, 80) }}</p>@endif</td>
                                <td class="px-5 py-4 text-xs text-slate-500">{{ \Illuminate\Support\Carbon::parse($log->created_at)->locale('fa')->isoFormat('YYYY/MM/DD HH:mm') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-10 text-center text-slate-500">هنوز اعلانی ثبت نشده است.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($logs->hasPages())<div class="border-t border-[#eeeaf5] p-5">{{ $logs->links() }}</div>@endif
        </section>
    </main>
</body>
</html>
