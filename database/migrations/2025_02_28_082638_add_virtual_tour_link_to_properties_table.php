<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) {
            if (!Schema::hasColumn('properties', 'virtual_tour_link')) { // ✅ Check before adding
                $table->string('virtual_tour_link')->nullable(); // ✅ Remove ->after('images')
            }
        });
    }
    

    public function down()
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('virtual_tour_link');
        });
    }
};
