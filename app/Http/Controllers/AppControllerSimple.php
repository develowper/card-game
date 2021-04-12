<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Divar;
use App\Follower;
use App\Queue;
use App\Ref;
use Carbon\Carbon;
use Helper;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class AppControllerSimple extends Controller
{

    public function __construct()
    {
        error_reporting(1);
        set_time_limit(-1);
        header("HTTP/1.0 200 OK");
        date_default_timezone_set('Asia/Tehran');

    }


    protected function sendError(Request $request)
    {
        $message = $request->message;


        $this->sendMessage(Helper::$logs[0], "■ Error!\n" . $request->header('User-Agent'), null, null, null);
        $this->sendMessage(Helper::$logs[0], "\n\n $message", null, null, null);

    }

    protected function testMode()
    {
        return ['test' => Helper::$test];

    }


    protected function getDivar(Request $request)
    {
        $name = $request->name;
        $group_id = $request->group_id;
        $paginate = $request->paginate ?? 24;
        $page = $request->page ?? 1;
        $sortBy = $request->sortBy ?? 'start_time';
        $direction = $request->direction ?? 'DESC';
        $query = Divar::query();

        if ($group_id)
            $query = $query->where('group_id', $group_id);
        if ($name)
            $query = $query->where('name', 'like', $name . '%');

        return $query->orderby('is_vip', 'DESC')->orderby($sortBy, $direction)->
        select(['id', 'user_id', 'chat_id', 'chat_username', 'chat_type', 'chat_title', 'chat_description', 'chat_main_color', 'is_vip', 'expire_time'])
            ->paginate($paginate, ['*'], 'page', $page);


    }

    protected function newChat(Request $request)
    {
        $group_id = $request->group_id;

        $chat_username = "@" . str_replace("@", "", $request->chat_username);

//        if (auth()->user()->score < Helper::$install_chat_score)
//            return "LOW_SCORE";
        if (Chat::where("chat_username", $chat_username)->exists())
            return ['res' => "CHAT_EXISTS"];

        $role = $this->getUserInChat(['chat_id' => $chat_username, 'user_id' => Helper::$bot_id,]);
        if ($role != 'administrator' && $role != 'creator' && auth()->user()->role != 'ad')
            return ['res' => "BOT_NOT_ADMIN"];

//        $role = $this->getUserInChat(['chat_id' => $chat_username, 'user_id' => auth()->user()->telegram_id,]);
//        if ($role != 'creator' && $role != 'administrator')
//            return ['res' => "NOT_ADMIN_OR_CREATOR"];

        $info = $this->getChatInfo($chat_username);
        if (!$info)
            return ['res' => "CHAT_NOT_FOUND"];

//        if ($info->type == 'channel') {
//            $tmp = auth()->user()->channels;
//            array_push($tmp, $chat_username);
//            auth()->user()->channels = $tmp;
//        } else {
//            $tmp = auth()->user()->groups;
//            array_push($tmp, $chat_username);
//            auth()->user()->groups = $tmp;
//        }


        $this->createChatImage($info->photo, "$info->id");

        $chat = Chat::create([
            'user_id' => auth()->user()->id,
            'group_id' => $group_id,
            'user_telegram_id' => auth()->user()->telegram_id,
            'chat_id' => "$info->id",
            'chat_type' => $info->type,
            'chat_username' => '@' . $info->username,
            'chat_main_color' => simple_color_thief(storage_path("app/public/chats/$info->id.jpg")),
            'chat_title' => $info->title,
            'chat_description' => $info->description,
        ]);

        if (in_array("$info->id", Divar::where('expire_time', '>=', Carbon::now())->pluck('chat_id')->toArray())) {
            return ['res' => "EXISTS_IN_DIVAR"];
        }
        $d = Divar::create(['user_id' => auth()->user()->id, 'group_id' => $group_id, 'chat_id' => "$chat->chat_id", 'chat_type' => $chat->chat_type,
            'chat_username' => $chat->chat_username, 'chat_title' => $chat->chat_title,
            'chat_description' => $chat->chat_description, 'chat_main_color' => $chat->chat_main_color, 'is_vip' => false, 'expire_time' => null, 'start_time' => Carbon::now()]);

        $first_name = auth()->user()->name;
        $from_id = auth()->user()->telegram_id;
        $chat_username = $chat->chat_username;

        foreach (Helper::$logs as $log)
            $this->sendMessage($log, "■  کاربر [$first_name](tg://user?id=$from_id) کانال/گروه $chat_username را وارد دیوار کرد  .", 'MarkDown', null, null);

        $ref = Ref::where('new_telegram_id', $from_id)->first();
        if ($ref) {
            $user = User::where('telegram_id', $ref->invited_by)->first();
            if ($user) {
                $ref_score = Helper::$ref_score;
                $user->score += $ref_score;
                $user->save();
                $this->sendMessage($ref->invited_by, "■  کاربر [$first_name](tg://user?id=$from_id)  را وارد دیوار کرد و $ref_score سکه به شما اضافه شد! $chat_username .", 'MarkDown', null, null);
            }
        }
//        auth()->user()->score -= Helper::$install_chat_score;
//        auth()->user()->save();


        return ['res' => 'SUCCESS_DIVAR', 'score' => auth()->user()->score,];


    }

    protected function addToDivar(Request $request)
    {
        $chat_id = $request->chat_id;
        $time = $request->time;
        $vip = $request->is_vip ? Helper::$vip_score : 0;
        $agree_queue = $request->agree_queue;
        // return $agree_queue;

//check time is valid
        if (!in_array($time, array_keys(Helper::$divar_scores)))
            return null;

        if ($vip > 0 && !$agree_queue && Divar::where('is_vip', true)->where('expire_time', '>=', Carbon::now())->count() >= Helper::$vip_count) {
            return ['res' => "VIP_FULL"];
        }

        if (auth()->user()->score < Helper::$divar_scores[$time] + $vip)
            return ['res' => "LOW_SCORE"];


        $chat = Chat::where('chat_id', $chat_id)->first();
        $role = $this->getUserInChat(['chat_id' => $chat_id, 'user_id' => auth()->user()->telegram_id]);

        if (($role != 'administrator' && $role != 'creator') || !$chat)
            return ['res' => "NOT_ADMIN"];


        if (in_array($chat_id, Queue::pluck('chat_id')->toArray())) {
            return ['res' => "EXISTS_IN_QUEUE"];
        }


        if (Divar::count() < Helper::$divar_show_items) {
            $d = Divar::create(['user_id' => auth()->user()->id, 'chat_id' => "$chat_id", 'chat_type' => $chat->chat_type, 'chat_username' => $chat->chat_username,
                'chat_title' => $chat->chat_title, 'chat_description' => $chat->chat_description, 'chat_main_color' => $chat->chat_main_color, 'is_vip' => $vip > 0 ? true : false, 'expire_time' => Carbon::now()->addMinutes($time), 'start_time' => Carbon::now()]);

            $first_name = auth()->user()->name;
            $from_id = auth()->user()->telegram_id;
            $chat_username = '@' . $chat->chat_username;

            foreach (Helper::$logs as $log)
                $this->sendMessage($log, "■  کاربر [$first_name](tg://user?id=$from_id)  $chat_username را وارد دیوار کرد  .", 'MarkDown', null, null);

            $ref = Ref::where('new_telegram_id', $from_id)->first();
            if ($ref) {
                $user = User::where('telegram_id', $ref->invited_by)->first();
                if ($user) {
                    $ref_score = Helper::$ref_score;
                    $user->score += $ref_score;
                    $user->save();
                    $this->sendMessage($ref->invited_by, "■  کاربر [$first_name](tg://user?id=$from_id)  را وارد دیوار کرد و $ref_score سکه به شما اضافه شد! $chat_username .", 'MarkDown', null, null);
                }
            }

            auth()->user()->score -= (Helper::$divar_scores[$time] + $vip);
            auth()->user()->save();

            return ['res' => 'SUCCESS_DIVAR', 'score' => auth()->user()->score, 'is_vip' => $d->is_vip, 'expire_time' => $d->expire_time];
        } else {
            if ($agree_queue) {

                $q = Queue::create(['user_id' => auth()->user()->id, 'chat_id' => "$chat_id", 'chat_type' => $chat->chat_type, 'chat_username' => $chat->chat_username,
                    'chat_title' => $chat->chat_title, 'chat_description' => $chat->chat_description, 'chat_main_color' => $chat->chat_main_color, 'is_vip' => $vip > 0 ? true : false, 'show_time' => $time]);
                auth()->user()->score -= (Helper::$divar_scores[$time] + $vip);
                auth()->user()->save();
                return ['res' => 'SUCCESS_QUEUE', 'score' => auth()->user()->score, 'is_vip' => $q->is_vip];
            } else
                return ['res' => 'AGREE_QUEUE'];
        }


    }

    protected function addToVIP(Request $request)
    {
        $chat_id = $request->chat_id;
        $accept_queue = $request->accept_queue;
        $vip_time = $request->vip_time;

        if ($vip_time && auth()->user()->score < Helper::$divar_scores[$vip_time])
            return ['res' => "LOW_SCORE"];


        $chat = Chat::where('chat_id', $chat_id)->first();
        if (!$chat)
            return ['res' => "CHAT_NOT_EXISTS"];
        $d = Divar::where('chat_id', $chat_id)->first();
        if (!$d) {

            $d = Divar::create(['user_id' => auth()->user()->id, 'group_id' => $chat->group_id, 'chat_id' => "$chat_id", 'chat_type' => $chat->chat_type, 'chat_username' => $chat->chat_username,
                'chat_title' => $chat->chat_title, 'chat_description' => $chat->chat_description, 'chat_main_color' => $chat->chat_main_color, 'is_vip' => false, 'expire_time' => null, 'start_time' => Carbon::now()]);
        }

        if ($d->is_vip || Queue::where('chat_id', $chat_id)->exists())
            return ['res' => "IS_VIP_BEFORE"];

        $vip_divars = Divar::where('is_vip', true)->get();
        $count_vip_divars = count($vip_divars);
        $removed = 0;
        foreach ($vip_divars as $vip_divar)
            if ($vip_divar->expire_time < Carbon::now()) {
                $vip_divar->is_vip = false;
                $vip_divar->save();
                $removed++;
            }
        $count_vip_divars -= $removed;
        if ($count_vip_divars < Helper::$vip_count) {
            // pop from queue to divar
            $vip_queues = Queue::where('is_vip', true)->take(Helper::$vip_count)->get();

            foreach ($vip_queues as $vip_queue) {
                if ($count_vip_divars >= Helper::$vip_count) break;
                Divar::create(['user_id' => $vip_queue->user_id,
                    'chat_id' => $vip_queue->chat_id,
                    'group_id' => $vip_queue->group_id,
                    'chat_type' => $vip_queue->chat_type,
                    'chat_username' => $vip_queue->chat_username,
                    'chat_title' => $vip_queue->chat_title,
                    'chat_description' => $vip_queue->chat_description,
                    'is_vip' => true,
                    'expire_time' => Carbon::now()->addMinutes($vip_queue->show_time),
                    'start_time' => Carbon::now()]);
                $vip_queue->delete();
                $count_vip_divars++;
            }
        }
        if ($count_vip_divars >= Helper::$vip_count && !$accept_queue)
            return ['res' => "VIP_FULL"];
        else if ($accept_queue) {
            $q = Queue::create(['user_id' => auth()->user()->id, 'group_id' => $chat->group_id, 'chat_id' => "$chat_id", 'chat_type' => $chat->chat_type, 'chat_username' => $chat->chat_username,
                'chat_title' => $chat->chat_title, 'chat_description' => $chat->chat_description, 'chat_main_color' => $chat->chat_main_color, 'is_vip' => true, 'show_time' => $vip_time]);
            return ['res' => "SUCCESS_QUEUE", 'score' => auth()->user()->score];
        } else {
            $d->is_vip = true;
            $d->expire_time = Carbon::now()->addMinutes($vip_time);
            $d->save();
            auth()->user()->score -= Helper::$divar_scores[$vip_time];
            auth()->user()->save();
            return ['res' => "VIP_SUCCESS", 'score' => auth()->user()->score, 'expire_time' => $d->expire_time];
        }


    }

    protected function getUserChats(Request $request)
    {
        $what = $request->what;

        if (!$what) {
            $chats = Chat::where('user_id', auth()->user()->id)->get();
            foreach ($chats as $chat) {
                $d = Divar::where('chat_id', $chat->chat_id)->first();

                $chat->expire_time = -1;
                $chat->in = null;
                $chat->is_vip = false;
                if ($d) {
                    $chat->in = 'd';
//                    return json_encode([$d->expire_time, Carbon::now()->timestamp]);
                    if ($d->expire_time < Carbon::now()->timestamp) {

                        $d->is_vip = false;
                        $d->save();
                    }
                    $chat->expire_time = $d->expire_time ?? 0;
                    $chat->is_vip = $d->is_vip;
                    if ($d->is_vip)
                        continue;
                }
                $q = Queue::where('chat_id', $chat->chat_id)->first();
                if ($q) {
                    $chat->in = 'q';
                    $chat->is_vip = $q->is_vip;
                }
            }
            return $chats;
        }
    }


    protected
    function getChatInfo($chat_id)
    {
        $res = $this->creator('getChat', [
            'chat_id' => $chat_id,

        ]);
        if (isset($res->result))
            return $res->result;
        else return null;
    }

    protected
    function getUserInChat($request)
    {
        $role = $this->creator('getChatMember', [
            'chat_id' => $request['chat_id'],
            'user_id' => $request['user_id']
        ]);
        $role = $role ? isset($role->result) ? isset($role->result->status) ? $role->result->status : $role->description : $role->description : null;
        return $role;
    }


    protected
    function updateScore(Request $request)
    {
//        $user = User::where("id", $request->id)->first();
        $user = auth()->user();
        if ($user)
            switch ($request->command) {
                case  'install_chat':
                    auth()->user()->score += Helper::$install_chat_score;
                    auth()->user()->save();
                    return auth()->user()->score;
                    break;
                case  'follow_chat':
                    auth()->user()->score += Helper::$follow_score;
                    auth()->user()->save();
                    return auth()->user()->score;

                    break;
                case  'see_video':
                    auth()->user()->score += Helper::$see_video_score;
                    auth()->user()->save();
                    return auth()->user()->score;
                    break;

            }
    }


    function sendMessage($chat_id, $text, $mode, $reply = null, $keyboard = null, $disable_notification = false)
    {
        return $this->creator('sendMessage', [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $mode,
            'reply_to_message_id' => $reply,
            'reply_markup' => $keyboard,
            'disable_notification' => $disable_notification,
        ]);
    }

    private
    function creator($method, $datas = [])
    {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        $res = curl_exec($ch);

        if (curl_error($ch)) {
            return (curl_error($ch));
        } else {
            return json_decode($res);
        }
    }

    private
    function createChatImage($photo, $chat_id)
    {
        if (!isset($photo) || !isset($photo->big_file_id)) return;
        $client = new \GuzzleHttp\Client();
        $res = $this->creator('getFile', [
            'file_id' => $photo->big_file_id,

        ])->result->file_path;

        $image = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $res;
        Storage::put("public/chats/$chat_id.jpg", $client->get($image)->getBody());

    }
}
