<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model {
    use HasFactory;

    protected $fillable = [
        'title',
        'department',
        'description',
        'responsibilities',
        'qualifications',
        'job_type',
        'salary_min',
        'salary_max',
        'application_deadline',
        'status',
        'image',
    ];
    protected $casts = [
        'description' => 'string',
        'responsibilities' => 'string',
        'qualifications' => 'string',
    ];
    
}
