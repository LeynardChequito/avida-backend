<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyMediaTable extends Migration
{
    public function up()
    {
        Schema::create('property_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade'); // ✅ Linked to properties table
            $table->string('url'); // ✅ Stores media path
            $table->enum('type', ['image', 'video', '360']); // ✅ Identifies media type
            $table->timestamps();
        });
    }
    

    public function down()
    {
        Schema::dropIfExists('property_media');
    }
}

