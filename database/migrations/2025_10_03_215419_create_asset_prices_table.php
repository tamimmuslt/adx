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
       Schema::create('asset_prices', function (Blueprint $table) {
    $table->id();

    // الأصل المرتبط
    $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();

    // بيانات الشمعة
    $table->decimal('open', 20, 8);   // سعر الفتح
    $table->decimal('high', 20, 8);   // أعلى سعر
    $table->decimal('low', 20, 8);    // أقل سعر
    $table->decimal('close', 20, 8);  // سعر الإغلاق

    // وقت بداية الشمعة (من Binance بيجي بالـ milliseconds)
    $table->bigInteger('open_time')->unsigned();

    // وقت إضافي لو بدك تخزن UTC timestamp
    $table->timestamp('timestamp')->nullable();

    // Laravel timestamps
    $table->timestamps();

    // قيود لتحسين الأداء
    $table->unique(['asset_id', 'open_time']);   // ما يتكرر نفس الأصل + نفس الشمعة
    $table->index(['asset_id', 'timestamp']);    // للبحث السريع
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_prices');
    }
};

      

