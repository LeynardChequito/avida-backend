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
        'features_amenities', 'status'
    ];

    protected $casts = [
        'features_amenities' => 'array',
    ];

    // ✅ Relationship: Property has many Media Files
    public function media()
    {
        return $this->hasMany(PropertyMedia::class, 'property_id');
    }

    // ✅ Separate Panolens (360° images)
    public function getPanolensImagesAttribute()
    {
        return $this->media()
            ->where('type', '360')
            ->get()
            ->map(function ($item) {
                return [
                    'url' => url("storage/" . ltrim($item->url, '/')),
                    'type' => '360'
                ];
            });
    }

    // ✅ Separate Lightbox2 (Images & Videos)
    public function getLightbox2MediaAttribute()
    {
        return $this->media()
            ->whereIn('type', ['image', 'video'])
            ->get()
            ->map(function ($item) {
                return [
                    'url' => url("storage/" . ltrim($item->url, '/')),
                    'type' => $item->type
                ];
            });
    }
}
