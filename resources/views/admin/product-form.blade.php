<!doctype html>
<html lang="fa" dir="rtl">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $product->exists ? 'ویرایش محصول' : 'محصول جدید' }} | Etod Graphic</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="min-h-screen bg-[#f7f5fc] text-[#171326]">
<header class="border-b border-[#ebe7f4] bg-white"><div class="mx-auto flex h-20 max-w-6xl items-center justify-between px-5 lg:px-8"><div><a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-violet-700">Etod Graphic</a><h1 class="text-xl font-black">{{ $product->exists ? 'ویرایش محصول' : 'محصول جدید' }}</h1></div><a href="{{ route('admin.products') }}" class="rounded-xl border border-[#e8e3f1] px-4 py-2 text-sm font-bold text-violet-700">بازگشت</a></div></header>
<main class="mx-auto max-w-6xl px-5 py-8 lg:px-8">
    @if(session('success'))<div class="mb-5 rounded-2xl bg-green-50 p-4 font-bold text-green-700">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="mb-5 rounded-2xl bg-red-50 p-4 font-bold text-red-700">{{ $errors->first() }}</div>@endif
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-3xl bg-white p-6 shadow-sm">
            <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST">
                @csrf
                @if($product->exists) @method('PATCH') @endif
                <div class="grid gap-5 sm:grid-cols-2">
                    <label class="sm:col-span-2"><span class="text-sm font-bold">نام محصول</span><input name="name" value="{{ old('name', $product->name) }}" required class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3"></label>
                    <label><span class="text-sm font-bold">Slug</span><input name="slug" value="{{ old('slug', $product->slug) }}" required class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3" dir="ltr"></label>
                    <label><span class="text-sm font-bold">دسته‌بندی</span><select name="category_id" required class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3">@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                    <label><span class="text-sm font-bold">قیمت پایه</span><input name="base_price" type="number" min="0" value="{{ old('base_price', $product->base_price ?? 0) }}" required class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3"></label>
                    <label><span class="text-sm font-bold">قیمت چاپ</span><input name="printing_price" type="number" min="0" value="{{ old('printing_price', $product->printing_price ?? 0) }}" required class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3"></label>
                    <label><span class="text-sm font-bold">وضعیت</span><select name="status" class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3"><option value="draft" @selected(old('status', $product->status) === 'draft')>پیش‌نویس</option><option value="published" @selected(old('status', $product->status) === 'published')>منتشرشده</option><option value="archived" @selected(old('status', $product->status) === 'archived')>بایگانی</option></select></label>
                    <label class="sm:col-span-2"><span class="text-sm font-bold">توضیحات</span><textarea name="description" rows="5" class="mt-2 w-full rounded-xl border border-[#e5e0ef] px-4 py-3">{{ old('description', $product->description) }}</textarea></label>
                </div>
                <button class="mt-6 rounded-xl bg-violet-700 px-6 py-3 font-black text-white">ذخیره محصول</button>
            </form>
        </section>
        <aside class="rounded-3xl bg-white p-6 shadow-sm"><h2 class="text-lg font-black">تصاویر محصول</h2>
            @if($product->exists)
                <form action="{{ route('admin.products.images.store', $product) }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-3">@csrf<input name="image" type="file" accept="image/jpeg,image/png,image/webp" required class="w-full rounded-xl border border-[#e5e0ef] p-3"><input name="alt_text" placeholder="متن جایگزین" class="w-full rounded-xl border border-[#e5e0ef] px-3 py-2"><label class="flex items-center gap-2 text-sm"><input name="is_primary" type="checkbox" value="1"> تصویر اصلی</label><button class="w-full rounded-xl bg-violet-700 px-4 py-3 font-bold text-white">آپلود تصویر</button></form>
                <div class="mt-6 space-y-3">@forelse($product->images as $image)<div class="flex items-center justify-between rounded-xl bg-[#fbfaff] p-3"><span class="text-xs font-bold">{{ $image->alt_text }}</span><form action="{{ route('admin.products.images.destroy', $image) }}" method="POST">@csrf @method('DELETE')<button class="text-xs font-bold text-red-600">حذف</button></form></div>@empty<p class="text-sm text-slate-500">هنوز تصویری اضافه نشده است.</p>@endforelse</div>
            @else
                <p class="mt-3 text-sm text-slate-500">ابتدا محصول را ذخیره کنید، سپس تصویر اضافه کنید.</p>
            @endif
        </aside>
    </div>
</main>
</body>
</html>
