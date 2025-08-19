<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectUser extends Model
{
    protected $table = 'subject_user';

    protected $fillable = [
        'user_id',
        'subject_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
