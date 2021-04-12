<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

// channels and groups in active countdown state
class Chat extends Model
{
    public $timestamps = false;
    protected $table = 'chats';
    protected $fillable = [
        'user_id', 'user_telegram_id', 'chat_id', 'group_id', 'chat_type', 'chat_username',
        'chat_main_color', 'chat_title', 'chat_description', 'active'
    ];
    protected $casts = [
        // 'chat_id' => 'string',
        //'expire_time' => 'timestamp',

    ];
}