<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixPropertyJsonFields extends Migration
{
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (Schema::hasColumn('properties', 'images')) { // ✅ Check before modifying
                $table->json('images')->nullable()->change();
            }
        });
    }
    
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            // ✅ Revert back to TEXT in case of rollback
            $table->text('images')->nullable()->change();
            $table->text('features_amenities')->nullable()->change();
        });
    }
}

