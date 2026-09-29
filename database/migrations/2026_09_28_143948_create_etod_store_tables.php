<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('hex_code', 7)->nullable();
            $table->timestamps();
        });

        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('base_price')->default(0);
            $table->unsignedBigInteger('printing_price')->default(0);
            $table->string('currency', 3)->default('IRR');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft')->index();
            $table->boolean('is_customizable')->default(true);
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('size_id')->nullable()->constrained()->nullOnDelete();
            $table->string('model')->nullable();
            $table->string('sku')->unique();
            $table->unsignedBigInteger('price')->nullable();
            $table->unsignedBigInteger('printing_price')->nullable();
            $table->unsignedInteger('stock')->default(0);
            $table->unsignedInteger('reserved_stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['product_id', 'color_id', 'size_id', 'model']);
        });

        Schema::create('print_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->decimal('physical_width', 8, 2)->nullable();
            $table->decimal('physical_height', 8, 2)->nullable();
            $table->decimal('print_area_width', 8, 2)->nullable();
            $table->decimal('print_area_height', 8, 2)->nullable();
            $table->decimal('print_area_x', 8, 2)->default(0);
            $table->decimal('print_area_y', 8, 2)->default(0);
            $table->unsignedSmallInteger('dpi')->default(300);
            $table->string('template_image_path')->nullable();
            $table->json('allowed_formats')->nullable();
            $table->unsignedInteger('max_image_size_kb')->default(10240);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('preview_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('sha256', 64)->index();
            $table->string('purpose')->default('customization');
            $table->timestamps();
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'session_id']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->restrictOnDelete();
            $table->foreignId('upload_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->json('customization_data')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->unsignedBigInteger('unit_price');
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_number')->unique();
            $table->string('status')->default('draft')->index();
            $table->string('payment_status')->default('pending');
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->text('customer_address')->nullable();
            $table->unsignedBigInteger('subtotal')->default(0);
            $table->unsignedBigInteger('printing_total')->default(0);
            $table->unsignedBigInteger('shipping_price')->default(0);
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('total_price')->default(0);
            $table->string('currency', 3)->default('IRR');
            $table->boolean('portfolio_consent')->default(false);
            $table->timestamp('consent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->json('variant_snapshot')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('printing_price')->default(0);
            $table->unsignedBigInteger('total_price');
            $table->json('customization_data')->nullable();
            $table->string('preview_image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('status')->default('pending')->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3)->default('IRR');
            $table->string('transaction_id')->nullable()->unique();
            $table->string('authority')->nullable()->index();
            $table->string('reference')->nullable();
            $table->json('callback_data')->nullable();
            $table->json('verification_data')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('quantity');
            $table->unsignedInteger('stock_after');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'notifications', 'reviews', 'inventory_movements', 'payments', 'order_items', 'orders', 'cart_items', 'carts', 'uploads', 'print_templates', 'product_variants', 'products', 'sizes', 'colors', 'categories'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
