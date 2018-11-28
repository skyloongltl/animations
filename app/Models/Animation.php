<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animation extends Model
{
    protected $table = 'animation_information';
    protected $fillable = [
        'animations_id',
        'url',
        'index',
        'web_type_id'
    ];
}
