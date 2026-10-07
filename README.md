# Etod Graphic

فروشگاه فارسی و RTL چاپ سفارشی سابلیمیشن با Laravel 12، MySQL و Vite.

## وضعیت بازسازی

- Laravel 12.69.2 و PHP 8.2
- UI فارسی/RTL با تم سفید، بنفش و یاسی
- کاتالوگ، دسته‌بندی، Variant، رنگ، سایز و Template چاپ
- سبد خرید مهمان با محاسبه قیمت سمت Backend
- قفل ردیفی موجودی و جلوگیری از Overselling
- آپلود خصوصی تصویر با بررسی MIME، ابعاد، حجم و SHA-256
- Customizer پایه: پیش‌نمایش، Drag & Drop، تغییر اندازه و ذخیره مختصات
- پنل محلی سفارش، کاتالوگ و تصاویر محصول
- مدیریت تنوع‌ها و قالب‌های چاپ، صف تولید و گردش انبار
- بایگانی محصول با ثبت دلیل؛ سوابق به‌صورت خودکار حذف نمی‌شوند

پنل `/admin` فعلاً بدون احراز هویت واقعی و فقط در محیط local/testing فعال است. آن را در شبکه یا Production در دسترس قرار ندهید. احراز هویت نهایی، درگاه واقعی و پیامک/واتساپ هنوز پیاده‌سازی نشده‌اند.

## راه‌اندازی محلی

1. XAMPP Control Panel را با Run as administrator باز کنید.
2. Apache و MySQL را Start کنید.
3. دیتابیس را بسازید:

```sql
CREATE DATABASE etod_graphic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. تنظیمات `.env` را بررسی کنید:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=etod_graphic
DB_USERNAME=root
DB_PASSWORD=
```

5. دستورات پروژه:

```powershell
composer install
npm install
php artisan migrate:fresh --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

آدرس: `http://127.0.0.1:8000/`

## تست

تست‌ها روی SQLite موقت اجرا می‌شوند و دیتابیس توسعه MySQL را پاک نمی‌کنند:

```powershell
php artisan test
vendor\bin\pint app database routes tests --test
```

## تنظیمات محلی PHP

برای Customizer و تست تصویر، این افزونه‌ها فعال هستند:

- `gd`
- `intl`
- `fileinfo`
- `pdo_mysql`
- `mbstring`
- `zip`
- `bcmath`
- `exif`

## وضعیت پرداخت و پیامک

درگاه پرداخت، SMS.ir و WhatsApp هنوز با Credential واقعی فعال نشده‌اند. حالت توسعه به‌صورت Mock/Log است و نباید در Production استفاده شود.
