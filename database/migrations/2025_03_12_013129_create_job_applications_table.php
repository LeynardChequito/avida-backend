<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number');
            $table->text('cover_letter')->nullable();
            $table->string('resume'); // Path to the uploaded file
            $table->string('linkedin_url')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('job_applications');
    }
};
