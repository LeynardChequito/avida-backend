<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inquiry_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->text('message');
            $table->string('sender'); // "Admin" or "User"
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('inquiry_replies');
    }
};
