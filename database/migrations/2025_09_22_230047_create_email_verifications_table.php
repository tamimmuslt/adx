<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code_hash'); // نخزن الهش وليس الكود عالطريق
            $table->timestamp('expires_at');
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->boolean('used')->default(false);
            $table->timestamps();
        });

        // index لتحسين البحث
        Schema::table('email_verifications', function (Blueprint $table) {
            $table->index(['user_id', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_verifications');
    }
};
