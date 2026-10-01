# راهنمای توسعه Etod Graphic

## اجرای محلی

```powershell
cd C:\xampp\htdocs\Etod-graphic-accio
php artisan serve --host=127.0.0.1 --port=8000
npm run dev
```

MySQL باید در XAMPP فعال باشد و دیتابیس `etod_graphic` وجود داشته باشد.

## پنل مدیریت فعلی

در نسخه فعلی، پنل فقط در محیط‌های `local` و `testing` Route می‌شود:

```text
http://127.0.0.1:8000/admin
http://127.0.0.1:8000/admin/orders
```

امکانات فعلی:

- داشبورد تعداد سفارش‌ها بر اساس وضعیت
- فهرست سفارش‌ها با Pagination
- مشاهده جزئیات مشتری و سفارش
- مشاهده Snapshot Variant و قیمت
- مشاهده داده Customizer
- تغییر وضعیت سفارش فقط به Transitionهای مجاز

این پنل هنوز Login، RBAC و Permission ندارد و برای Production قابل استفاده نیست. قبل از فعال‌سازی عمومی باید احراز هویت ادمین، Policy/Gate، RBAC، Audit Log و 2FA اضافه شود.

## تست

تست‌ها روی SQLite موقت اجرا می‌شوند تا `RefreshDatabase` دیتابیس توسعه MySQL را پاک نکند:

```powershell
php artisan test
vendor\bin\pint app database routes tests --test
npm run build
```
