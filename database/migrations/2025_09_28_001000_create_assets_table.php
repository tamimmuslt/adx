<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // اسم الأصل مثل Gold, Silver
            $table->string('symbol')->unique(); // الرمز مثل XAU/USD
            $table->enum('category', ['commodities', 'indices', 'stocks', 'crypto']);
            $table->timestamps(); // created_at و updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
