<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('deals', function (Blueprint $table) {
        $table->foreignId('user_id')->after('id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('asset_id')->after('order_id')->constrained('assets')->cascadeOnDelete();
        $table->enum('side', ['buy','sell'])->after('asset_id');
        $table->integer('lots')->after('side');
        $table->decimal('entry_price', 15, 2)->after('lots');
        $table->decimal('close_price', 15, 2)->nullable()->after('entry_price'); // اختياري
    });
}

};
