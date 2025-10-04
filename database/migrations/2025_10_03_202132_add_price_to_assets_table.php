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
    Schema::table('assets', function (Blueprint $table) {
        $table->decimal('price', 20, 8)->nullable()->after('category');
    });
}

public function down(): void
{
    Schema::table('assets', function (Blueprint $table) {
        $table->dropColumn('price');
    });
}

};
