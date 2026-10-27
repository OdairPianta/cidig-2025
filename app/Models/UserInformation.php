<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    protected $table = 'user_informations';

    protected $fillable = [
        'user_id',
        'university_id',
        'picture_url',
        'lattes_url',
        'orcid_url',
        'linkedin_url',
        'minibio',
        'description',
        'title',
        'website',
        'recovery_email',
        'roles',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'roles' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function getPictureLinkAttribute()
    {
        if (! $this->picture_url) {
            return '';
        }

        return \App\Support\Uploads::url($this->picture_url);
    }
}
