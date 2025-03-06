<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class MigratePropertyImagesToMediaTable extends Migration
{
    public function up()
{
    Schema::table('properties', function (Blueprint $table) {
        if (Schema::hasColumn('properties', 'images')) { // ✅ Check if column exists before dropping
            $table->dropColumn('images');
        }
    });
}


    public function down()
    {
        // ✅ Restore images column if rollback is needed
        Schema::table('properties', function ($table) {
            $table->json('images')->nullable();
        });
    }
}