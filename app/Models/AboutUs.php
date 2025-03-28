<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name', 'established_year', 'parent_company', 'company_slogan',
        'brief_intro', 'mission_statement', 'vision_statement',
        'our_story', 'milestones', 'evolution',
        'real_estate_services', 'property_types', 'investment_opportunities',
        'customer_segments', 'quality_innovation', 'prime_locations',
        'affordability_financing', 'sustainability', 'awards',
        'contact_address', 'phone_numbers', 'email_support', 'live_chat',
        'social_media_links', 'status', 
        // 'company_logo',
         'office_images', 'version_history'
    ];
}