<?php

namespace App\Console\Commands;

use App\Divar;
use App\Queue;
use App\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdateDivar extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:updatedivar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'حذف گروه/کانال منقضی شده';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //delete expired from divar
        //add from divar

        $current = Carbon::now();
        $nums = Divar::where('expire_time', '<', $current)->delete();
        $queue = Queue::take($nums)->get();
        foreach ($queue as $item) {
            Divar::create(['user_id' => $item->user_id,
                'chat_id' => $item->chat_id,
                'chat_type' => $item->chat_type,
                'chat_username' => $item->chat_username,
                'chat_title' => $item->chat_title,
                'chat_description' => $item->chat_description,
                'expire_time' => Carbon::now()->addMinutes($item->show_time),
                'start_time' => $current]);

            $this->sendMessage(User::find($item->user_id)->telegram_id, "گروه/کانال $item->chat_username هم اکنون در دیوار قرار گرفت!");
        }
//
    }

    protected function sendMessage($chat_id, $text)
    {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . 'SendMessage';
        $datas = [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'reply_to_message_id' => null,
            'reply_markup' => null
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        $res = curl_exec($ch);

        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        } else {
            return json_decode($res);
        }


    }
}
