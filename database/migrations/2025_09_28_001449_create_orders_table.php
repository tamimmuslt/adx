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
            $table->integer('lots');
            $table->integer('leverage');
            $table->decimal('take_profit', 15, 2)->nullable();
            $table->decimal('stop_loss', 15, 2)->nullable();
            $table->enum('status', ['pending', 'executed', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
