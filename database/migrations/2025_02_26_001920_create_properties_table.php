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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone_number');
            $table->enum('type', ['Owner', 'Agent', 'Broker']);
            $table->string('property_name');
            $table->string('location');
            $table->enum('unit_type', ['Studio Room', '1BR', '2BR', '3BR', 'Loft', 'Penthouse']);
            $table->enum('unit_status', ['Bare', 'Semi-Furnished', 'Fully-Furnished', 'Interiored']);
            $table->decimal('price', 10, 2);
            $table->integer('square_meter');
            $table->integer('floor_number');
            $table->enum('parking', ['With Parking', 'No Parking']);
            $table->enum('property_status', ['For Rent', 'For Sale']);
            $table->json('features_amenities');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // ✅ Property Approval Status
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
