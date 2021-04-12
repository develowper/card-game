<?php

namespace App\Console\Commands;

use App\Divar;
use App\Tab;
use Carbon\Carbon;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendTabsToChannels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tab:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make automatic tab list and send to channels';

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
//create tabs from divar bot is admin and member >20

        $tabs = DB::table('queue')->whereNotNull('divar_to_tab')->get();
        if (count($tabs) == 0) return;


        $divars = Divar::whereIn('chat_id', array_column($tabs, 'divar_to_tab'))->get();

        Helper::sendMessage(Helper::$logs[0], "Divar To Tab " . count($divars), null);


        foreach ($divars as $d) {
            $count = $this->getChatMembersCount($d->chat_id);
            if ($count >= 20 && $this->botIsAdminAndHasPrivileges($d->chat_id)) {
                Tab::create(['chat_id' => "$d->chat_id", 'members' => $count, 'user_id' => $d->user_id, 'created_at' => Carbon::now(), 'inserted' => false, 'cluster_id' => null]);
                DB::table('queue')->where('divar_to_tab', $d->chat_id)->delete();
            }
        }
        Helper::sendMessage(Helper::$logs[0], "Divar To Tab Completed!" . count($divars), null);

    }

    private
    function getChatMembersCount($chat_id)
    {
        $res = Helper::creator('getChatMembersCount', ['chat_id' => $chat_id,]);
        if ($res->ok == true)
            return (int)$res->result; else return 0;
    }

    private
    function botIsAdminAndHasPrivileges($chat_id)
    {


        $res = Helper::creator('getChatMember', [
            'chat_id' => $chat_id,
            'user_id' => Helper::$bot_id
        ]);
        if ($res->ok == false)
            return false;// $res->description;
        elseif ($res->result->status != "administrator" ||
            !$res->result->can_post_messages ||
            !$res->result->can_edit_messages ||
            !$res->result->can_delete_messages)
            return false;


    }
}
