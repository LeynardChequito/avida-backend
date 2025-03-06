<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->enum('status', ['pending', 'on_process', 'done'])->default('pending');
        });
    }

    public function down() {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
