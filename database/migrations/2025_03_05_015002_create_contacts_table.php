<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable(false);
            $table->string('main_phone')->nullable();
            $table->string('sales_phone')->nullable();
            $table->string('leasing_phone')->nullable();
            $table->string('employment_phone')->nullable();
            $table->string('customer_care_phone')->nullable();
            $table->string('customer_care_landline')->nullable();
            $table->string('email');
            $table->string('support_email')->nullable();
            $table->string('business_hours')->nullable();
            $table->string('facebook_link')->nullable();
            $table->string('instagram_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('linkedin_link')->nullable();
            $table->string('tiktok_link')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('contacts');
    }
};
