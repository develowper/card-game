<?php

namespace App\Console\Commands;

use App\Divar;
use App\Tab;
use Carbon\Carbon;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendDivarToTabQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tab:queue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send From Divar To Queue For Check For Tab (ready for tabCreator)';

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
        //delete expired Chats
//        $expired = Divar::where('expire_time', '<', Carbon::now())->get();
//        foreach ($expired as $d) {
//            $this->DeleteMessage(Helper::$divarChannel, $d->message_id);
//            $d->delete();
//        }

        $divars = Divar::distinct('chat_id')->pluck('chat_id');

        DB::table('queue')->insert(array_map(function ($chat_id) {
            return ['divar_to_tab' => $chat_id];
        }, $divars))->get();


        Helper::sendMessage(Helper::$logs[0], "Divar To Queue " . count($divars), null);


    }

    private
    function DeleteMessage($chatid, $massege_id)
    {
        Helper::creator('DeleteMessage', [
            'chat_id' => $chatid,
            'message_id' => $massege_id
        ]);
    }


}
