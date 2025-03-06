<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryReply extends Model
{
    use HasFactory;

    protected $fillable = ['inquiry_id', 'email', 'message', 'sender'];

    public function inquiry()
    {
        return $this->belongsTo(Inquiry::class);
    }
}
