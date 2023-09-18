<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscoveryMotorcycle extends Model
{
    use HasFactory;

    protected $table = 'wpsgshcm_specs';

    protected $fillable = [
        'id',
        'motorcycle_id',
        'name',
        'year',
        'description',
        'bodywork',
        'transmission',
        'highways',
        'long_trips',
        'experience',
        'specs',
        'image',
        'endpoint',
        'active'
    ];
}
