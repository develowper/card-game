<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Waiting extends Model
{

    public $timestamps = false;
    protected $table = 'waitings';
    protected $fillable = [
        'p_id', 'p_username', 'game_id', 'created_at'

    ];
    protected $casts = [


    ];
}
