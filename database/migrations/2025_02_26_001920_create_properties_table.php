<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('property_submissions', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone_number', 13)->comment('Must be a valid PH number format');
            $table->enum('type', ['Owner', 'Agent', 'Broker']);

            // Property Information
            $table->string('property_name');
            $table->string('location');
            $table->enum('unit_type', ['Studio Room', '1BR', '2BR', '3BR', 'Loft', 'Penthouse']);
            $table->enum('unit_status', ['Bare', 'Semi-Furnished', 'Fully-Furnished', 'Interiored']);
            $table->decimal('price', 10, 2);
            $table->decimal('square_meter', 8, 2);
            $table->integer('floor_number');
            $table->enum('parking', ['With Parking', 'No Parking']);
            $table->enum('property_status', ['For Rent', 'For Sale']);

            // Features & Amenities
            $table->json('features_amenities');

            // Property Images
            $table->json('images');

            // Admin Approval Status
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_submissions');
    }
};
