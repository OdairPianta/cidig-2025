<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sponsor extends Model
{
    protected $table = 'sponsors';

    public $fillable = [
        'id',
        'name',
        'image',
    ];

    public function series()
    {
        return $this->belongsToMany(Serie::class, 'serie_sponsor', 'sponsor_id', 'serie_id');
    }
}
