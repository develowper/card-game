<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

// channels and groups in active countdown state
class UserChat extends Model
{

    public $timestamps = false;
    protected $table = 'user_chat';
    protected $fillable = [
        'user_id', 'chat_id'
    ];
}