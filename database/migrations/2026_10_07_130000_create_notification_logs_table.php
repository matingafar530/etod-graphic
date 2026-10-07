<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 30);
            $table->string('event');
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient', 30)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('sent')->index();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
