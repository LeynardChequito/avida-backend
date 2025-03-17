<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('department');
            $table->text('description');
            $table->text('responsibilities');
            $table->text('qualifications');
            $table->enum('job_type', ['Full-time', 'Part-time', 'Remote', 'On-site']);
            $table->decimal('salary_min', 10, 2)->nullable();
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->date('application_deadline');
            $table->enum('status', ['Published', 'Unpublished'])->default('Unpublished');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('jobs');
    }
};
