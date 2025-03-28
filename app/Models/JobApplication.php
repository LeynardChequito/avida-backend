<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model {
    use HasFactory;

    protected $fillable = [
        'job_id', 'full_name', 'email', 'phone_number', 'cover_letter', 'resume', 'linkedin_url', 'status', 'admin_reply'
    ];

    public function job() {
        return $this->belongsTo(Job::class);
    }
}
