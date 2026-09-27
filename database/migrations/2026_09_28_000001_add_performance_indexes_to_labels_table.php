<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah index performa pada production_date
        Schema::table('labels', function (Blueprint $table) {
            $table->index('production_date', 'labels_production_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('labels', function (Blueprint $table) {
            $table->dropIndex('labels_production_date_index');
        });
    }
};
