<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
            $table->foreignId('order_item_id')->nullable()->unique()->constrained('order_items')->cascadeOnDelete();
            $table->string('author_name')->nullable()->after('user_id');
            $table->timestamp('reviewed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['order_item_id']);
            $table->dropUnique(['order_item_id']);
            $table->dropColumn(['order_item_id', 'author_name', 'reviewed_at']);
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
