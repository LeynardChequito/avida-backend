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
        Schema::create('about_us', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Avida Land');
            $table->year('established_year')->nullable();
            $table->string('parent_company')->default('Ayala Land Inc.');
            $table->string('company_slogan')->default('Inspired Living, Avida Living');
            $table->text('brief_intro')->nullable();
            $table->text('mission_statement')->nullable();
            $table->text('vision_statement')->nullable();
            $table->text('our_story')->nullable();
            $table->text('milestones')->nullable();
            $table->text('evolution')->nullable();
            $table->text('real_estate_services')->nullable();
            $table->text('property_types')->nullable();
            $table->text('investment_opportunities')->nullable();
            $table->text('customer_segments')->nullable();
            $table->text('quality_innovation')->nullable();
            $table->text('prime_locations')->nullable();
            $table->text('affordability_financing')->nullable();
            $table->text('sustainability')->nullable();
            $table->text('awards')->nullable();
            $table->text('contact_address')->nullable();
            $table->string('phone_numbers')->nullable();
            $table->string('email_support')->nullable();
            $table->string('live_chat')->nullable();
            $table->json('social_media_links')->nullable();
            $table->enum('status', ['Published', 'Unpublished'])->default('Unpublished');
            $table->timestamps();
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_us');
    }
};
