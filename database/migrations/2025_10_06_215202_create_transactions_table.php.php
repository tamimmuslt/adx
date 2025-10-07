<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['deposit', 'withdraw', 'trade', 'fee']);
            $table->string('currency');                 // الجديد: نوع العملة أو الأصل
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->string('method')->nullable();        // الجديد: بنك، كريبتو، محفظة إلخ
            $table->enum('status', ['completed', 'pending', 'failed'])->default('pending'); // الجديد
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};


