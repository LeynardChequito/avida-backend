<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyMedia extends Model
{
    use HasFactory;

    protected $fillable = ['property_id', 'url', 'type'];

    // ✅ Relationship: Media belongs to a Property
    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    // ✅ Accessor to automatically format the storage URL
    public function getUrlAttribute($value)
    {
        return url("storage/" . ltrim($value, '/'));
    }
}
