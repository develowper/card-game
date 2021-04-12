<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Telegram\Bot\Actions;
use Telegram\Bot\Commands\Command;

// channels and groups in active countdown state
class Divar extends Model
{
    public $timestamps = false;
    protected $table = 'divar';
    protected $fillable = [
        'user_id', 'chat_id', 'group_id', 'chat_type', 'chat_username', 'chat_title', 'chat_description',
        'chat_main_color', 'start_time', 'expire_time', 'is_vip', 'message_id', 'follow_score',
    ];
    protected $casts = [
        // 'chat_id' => 'string',
        'expire_time' => 'timestamp',
        'start_time' => 'timestamp',
    ];
}