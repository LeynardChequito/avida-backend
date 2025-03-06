<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = ['first_name', 'last_name', 'email', 'phone', 'inquiry_type', 'message', 'status'];

    public function replies()
    {
        return $this->hasMany(InquiryReply::class);
    }
}