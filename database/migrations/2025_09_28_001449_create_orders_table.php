<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('asset_id')->constrained('assets')->onDelete('cascade');
            $table->enum('order_type', ['buy', 'sell']);
            
            // ✅ جعل اللوت الافتراضي 0.1
            $table->decimal('lots', 10, 2)->default(0.1);

            $table->integer('leverage')->default(1);
            $table->decimal('take_profit', 15, 2)->nullable();
            $table->decimal('stop_loss', 15, 2)->nullable();
            
            // ✅ إضافة الحقل الجديد
            $table->boolean('pending_order')->default(false);
            
            $table->enum('status', ['pending', 'executed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
