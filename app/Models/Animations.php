<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animations extends Model
{
    protected $table = 'animations_information';
    protected $fillable = [
        'image',
        'name',
        'index_show',
        'play',
        'is_finish',
        'episodes',
        'md5_name',
        'web_type_id'
    ];
}
