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
    Schema::table('job_applications', function (Blueprint $table) {
        $table->string('status')->default('Pending')->after('id'); // Add status
        $table->text('admin_reply')->nullable(); // Add admin_reply
    });
}

public function down()
{
    Schema::table('job_applications', function (Blueprint $table) {
        $table->dropColumn(['status', 'admin_reply']);
    });
}

};
