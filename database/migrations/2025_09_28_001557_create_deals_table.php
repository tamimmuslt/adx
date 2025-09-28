<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->decimal('executed_price', 15, 2);
            $table->integer('executed_lots');
            $table->decimal('pnl', 15, 2); // الربح أو الخسارة
            $table->timestamp('executed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
