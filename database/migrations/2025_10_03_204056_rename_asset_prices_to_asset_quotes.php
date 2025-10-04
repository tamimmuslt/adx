<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::rename('asset_prices', 'asset_quotes');
    }

    public function down(): void
    {
        Schema::rename('asset_quotes', 'asset_prices');
    }
};
