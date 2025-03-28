<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->text('content');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->longText('images')->nullable(); // stores JSON string
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news'); // ✅ Proper rollback for migration
    }
};
