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
            $table->text('admin_reply')->nullable();

        });
    }
    
    public function down()
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropColumn('admin_reply');
        });
    }
    
};
