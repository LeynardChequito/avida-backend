<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('properties', 'status')) { // ✅ Check if column exists before adding
            Schema::table('properties', function (Blueprint $table) {
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('images');

            });
        }
    }
    

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

