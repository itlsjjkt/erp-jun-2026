<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWarrantyColumnsToInventoryAssets extends Migration
{
    public function up()
    {
        Schema::table('inventory_assets', function (Blueprint $table) {
            $table->date('purchase_date')->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('vendor_name')->nullable();
        });

        // Status 2 (Deleted lama) bukan lagi status: pindah ke soft delete
        DB::table('inventory_assets')
            ->where('status', 2)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
    }

    public function down()
    {
        Schema::table('inventory_assets', function (Blueprint $table) {
            $table->dropColumn(['purchase_date', 'warranty_until', 'vendor_name']);
        });
    }
}
