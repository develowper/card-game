<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

// channels and groups in active countdown state
class Follower extends Model
{

    public $timestamps = false;
    protected $table = 'followers';
    protected $fillable = [
        'telegram_id', 'chat_id', 'user_id', 'chat_username', 'left', 'added_by', 'in_vip', 'follow_score'
    ];
}