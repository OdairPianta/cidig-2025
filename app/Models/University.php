<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function userInformations()
    {
        return $this->hasMany(UserInformation::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', '=', true);
    }

    public function scopeByName(Builder $query, string $name)
    {
        return $query->where('name', '=', $name);
    }

    public function scopeByIds(Builder $query, array $ids)
    {
        return $query->whereIn('id', $ids);
    }
}
