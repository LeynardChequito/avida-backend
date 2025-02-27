<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone_number', 'type',
        'property_name', 'location', 'unit_type', 'unit_status', 'price',
        'square_meter', 'floor_number', 'parking', 'property_status',
        'features_amenities', 'images', 'status'
    ];

    protected $casts = [
        'features_amenities' => 'array',
        'images' => 'array',
    ];
    
}
