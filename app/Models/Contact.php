<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model {
    use HasFactory;
    protected $fillable = [
        'name', 'address', 'main_phone', 'sales_phone', 'leasing_phone',
        'employment_phone', 'customer_care_phone', 'customer_care_landline',
        'email', 'support_email', 'business_hours',
        'facebook_link', 'instagram_link', 'youtube_link', 
        'linkedin_link', 'tiktok_link'
    ];
}