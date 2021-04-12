<?php

namespace App\Http\Controllers;

use App\Chat;
use App\Divar;
use App\Follower;
use App\Group;
use App\Queue;
use App\Ref;
use Carbon\Carbon;
use Helper;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpParser\Node\Expr\AssignOp\Div;

class AppController extends Controller
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


//        $this->sendMessage(Helper::$logs[0], "■ Error!\n" . $request->header('User-Agent'), null, null, null);
        $this->sendMessage(Helper::$logs[0], "\n\n $message", null, null, null);

    }

    protected function testMode()
    {
        return ['test' => Helper::$test];

    }

    protected function getSettings(Request $request)
    {
        if ($request->test == true)
            return Helper::$test;
        return ['divar_scores' => Helper::$divar_scores, 'vip_score' => Helper::$vip_score,
            'add_score' => Helper::$add_score, 'follow_score' => Helper::$follow_score,
            'install_chat_score' => Helper::$install_chat_score, 'see_video_score' => Helper::$see_video_score,
            'ref_score' => Helper::$ref_score, 'groups' => Group::select('id', 'name')->get()];
    }

    protected function getDivar(Request $request)
    {
        $name = $request->name;
        $group_id = $request->group_id;
        $paginate = $request->paginate ?? 24;
        $page = $request->page ?? 1;
        $sortBy = $request->sortBy ?? 'expire_time';
        $direction = $request->direction ?? 'DESC';
        $query = Divar::query();

        if ($group_id)
            $query = $query->where('group_id', $group_id);
        if ($name)
            $query = $query->where('name', 'like', $name . '%');

        $divars = $query->orderby('is_vip', 'DESC')->orderby($sortBy, $direction)->
        select(['id', 'user_id', 'chat_id', 'chat_username', 'chat_type', 'chat_title', 'chat_description', 'chat_main_color', 'is_vip', 'expire_time'])
            ->paginate($paginate, ['*'], 'page', $page);


        foreach ($divars as $d) {
            // $info = $this->getChatInfo(['chat_id' => "$d->chat_id"]);

//             $role = $this->getUserInChat(['chat_id' => $d->chat_id, 'user_id' => auth()->user()->telegram_id,]);
//             $role = $role ? isset($role->result) ? isset($role->result->status) ? $role->result->status : null : null : null;
            if (Follower::where('chat_id', $d->chat_id)->where('telegram_id', auth()->user()->telegram_id)->where('left', false)->exists())
                $d->role = 'member';
        }
        return $divars;
    }

    protected function newChat(Request $request)
    {
        $chat_username = "@" . str_replace("@", "", $request->chat_username);

        if (auth()->user()->score < Helper::$install_chat_score)
            return "LOW_SCORE";
        if (Chat::where("chat_username", $chat_username)->exists())
            return "CHAT_EXISTS";

        $role = $this->getUserInChat(['chat_id' => $chat_username, 'user_id' => Helper::$bot_id,]);
        if ($role != 'administrator' && $role != 'creator')
            return "BOT_NOT_ADMIN";

        $role = $this->getUserInChat(['chat_id' => $chat_username, 'user_id' => auth()->user()->telegram_id,]);
        if ($role != 'creator' && $role != 'administrator')
            return "NOT_ADMIN_OR_CREATOR";

        $info = $this->getChatInfo($chat_username);
        if (!$info)
            return "CHAT_NOT_FOUND";

        if ($info->type == 'channel') {
            $tmp = auth()->user()->channels;
            array_push($tmp, $chat_username);
            auth()->user()->channels = $tmp;
        } else {
            $tmp = auth()->user()->groups;
            array_push($tmp, $chat_username);
            auth()->user()->groups = $tmp;
        }


        auth()->user()->score -= Helper::$install_chat_score;
        auth()->user()->save();

        $this->createChatImage($info->photo, "$info->id");

        Chat::create([
            'user_id' => auth()->user()->id,
            'user_telegram_id' => auth()->user()->telegram_id,
            'chat_id' => "$info->id",
            'chat_type' => $info->type,
            'chat_username' => '@' . $info->username,
            'chat_main_color' => simple_color_thief(storage_path("app/public/chats/$info->id.jpg")),
            'chat_title' => $info->title,
            'chat_description' => $info->description,
        ]);


        return "REGISTER_SUCCESS";

    }

    protected function deleteChat(Request $request)
    {
        $chat_id = $request->chat_id;


        $chat = Chat::where('chat_id', "$chat_id")->first();
        if ($chat && ($chat->user_id == auth()->user()->id || auth()->user()->role == 'ad')) {
            Storage::delete("public/chats/$chat_id.jpg");
            $chat->delete();
            Divar::where('chat_id', "$chat_id")->delete();
            QUEUE::where('chat_id', "$chat_id")->delete();

            return ['res' => 'DELETE_SUCCESS'];
        }
        return ['res' => 'DELETE_FAILED'];


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

        if (in_array($chat_id, Divar::where('expire_time', '>=', Carbon::now())->pluck('chat_id')->toArray())) {
            return ['res' => "EXISTS_IN_DIVAR"];
        }
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

    protected function getUserChats(Request $request)
    {
        $what = $request->what;

        if (!$what) {
            $chats = Chat::where('user_id', auth()->user()->id)->get();
            foreach ($chats as $chat) {
                $d = Divar::where('chat_id', $chat->chat_id)->where('expire_time', '>=', Carbon::now())->first();

                $chat->expire_time = -1;
                $chat->in = null;
                $chat->is_vip = false;
                if ($d) {
                    $chat->in = 'd';
                    $chat->expire_time = $d->expire_time;
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

    protected function refreshChat(Request $request)
    {

        $group_id = $request->group_id;
        $chat_id = $request->chat_id;
        $chat = null;
        if ($chat_id) {
            $chat = Chat::where('chat_id', $chat_id)->first();

            if ($chat) {
                $info = $this->getChatInfo($chat_id);
                if ($info) {
                    $this->createChatImage($info->photo, "$info->id");
                    $chat->chat_main_color = simple_color_thief(storage_path("app/public/chats/$chat_id.jpg"));
                    $chat->chat_username = $info->username;
                    $chat->chat_title = $info->title;
                    $chat->chat_description = $info->description;
                    $chat->group_id = $group_id;
                    $chat->save();
                    $d = Divar::where('chat_id', $chat->chat_id)->where('expire_time', '>=', Carbon::now())->first();
                    $chat->expire_time = -1;
                    if ($d) {
                        $d->chat_username = $chat->chat_username;
                        $d->chat_title = $chat->chat_title;
                        $d->chat_description = $chat->chat_description;
                        $d->group_id = $group_id;
                        $d->save();
                        $chat->in = 'd';
                        $chat->expire_time = $d->expire_time;
                        $chat->is_vip = $d->is_vip;
                        return $chat;
                    }

                    $q = Queue::where('chat_id', $chat->chat_id)->first();
                    if ($q) {
                        $chat->in = 'q';
                        $chat->is_vip = $q->is_vip;
                        $chat->group_id = $group_id;
                        return $chat;
                    }
                }

            }

        }
        return $chat;
    }

    protected
    function viewChat(Request $request)
    {
        $chat_id = $request->chat_id;

        $item = Divar::where('chat_id', $chat_id)->first();

        if ($item && $item->expire_time < Carbon::now()->timestamp) {
            // $item->delete();
            return "TIMEOUT_CHAT";
        }

        $role = $this->getUserInChat(['chat_id' => $chat_id, 'user_id' => auth()->user()->telegram_id,]);


        //   return json_encode($role);
        if ($role == 'member' || $role == 'administrator' || $role == 'creator' || $role == 'left' || $role == 'kicked')
            return "VIEW";
        else if (strpos($role, "telegram") !== false)
            return "TELEGRAM_ERROR";
        else if (strpos($role, "kicked") !== false || strpos($role, "chat not") !== false || strpos($role, "user not") !== false)
            return "BOT_NOT_ADDED";
        else
            return $role;
    }

    protected
    function getUser(Request $request)
    {

        if ($request->for == 'me')
            return [auth()->user()->only(['id', 'name', 'telegram_username', 'telegram_id', 'role', 'channels', 'groups', 'score'])];
        if ($request->for == 'score')
            return ['score' => auth()->user()->score];
//        elseif (in_array(auth()->user()->telegram_id, Helper::$Dev))
//            return User::whereIn('id', $request->ids)->get();


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
    function checkUserJoined(Request $request)
    {
        $chat_id = $request->chat_id;
        $chat_username = $request->chat_username;
        $last_score = $request->last_score;
        $isChannel = $request->chat_type == 'channel' ? true : false;

        if (!Divar::where('chat_id', $chat_id)->where('expire_time', '>=', Carbon::now())->exists()) {
            return "TIMEOUT_CHAT";

        }
        $res = $this->getUserInChat(['chat_id' => $chat_id, 'user_id' => auth()->user()->telegram_id,]);

        $f = Follower::where('telegram_id', auth()->user()->telegram_id)->where('chat_id', $chat_id)->first();
        if ($res == 'member') {
            if ($isChannel) {
                if (!$f) {
                    Follower::create(['chat_id' => $chat_id, 'chat_username' => $chat_username,
                        'telegram_id' => auth()->user()->telegram_id, 'user_id' => auth()->user()->id]);

                    auth()->user()->score += Helper::$follow_score;
                    auth()->user()->save();
                    return 'MEMBER';
                } else {
                    //left or before register
                    return 'REPEATED_ADD';
                }
            } else { // group or supergroup
                if ($f && $f->left)
                    return 'REPEATED_ADD';
                else {
                    if (auth()->user()->score > $last_score) { // app not updated
                        return 'MEMBER';
                    } else {
                        if (!$f)
                            Follower::create(['chat_id' => $chat_id, 'chat_username' => $chat_username,
                                'telegram_id' => auth()->user()->telegram_id, 'user_id' => auth()->user()->id]);
                        return null;
                    }
                }
            }

        } elseif ($res == 'creator' || $res == 'administrator') {
            if (!$f)
                Follower::create(['chat_id' => $chat_id, 'chat_username' => $chat_username,
                    'telegram_id' => auth()->user()->telegram_id, 'user_id' => auth()->user()->id]);
            return "ADMIN_OR_CREATOR";

        } else if (strpos($res, "telegram") !== false)
            return "TELEGRAM_ERROR";
        else {

            return "BOT_NOT_ADDED_OR_NOT_EXISTS";

        }

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

    protected
    function leftUsersPenalty()
    {

        $user = auth()->user();
        if (in_array($user->telegram_id, Helper::$Dev)) {

            $user_chats = Chat::pluck('chat_username')->toArray();

        } else
            $user_chats = array_merge($user->groups, $user->channels);
        $left = 0;

        foreach (Follower::whereIn('chat_username', $user_chats)->where('left', false)->get() as $f) {
            $vip = $f->in_vip ? 2 : 1;

            if ($f->added_by) {
                $penalty_user = User::where('telegram_id', $f->added_by)->first();
                $left_score = Helper::$add_score * $vip;
            } else {
                $penalty_user = User::where('telegram_id', $f->telegram_id)->first();
                $left_score = Helper::$follow_score * $vip;
            }

            $role = $this->getUserInChat(['chat_id' => $f->chat_id, 'user_id' => $f->telegram_id]);
            usleep(rand(500, 1000));
            if ($role != 'member' && $role != 'creator' && $role != 'administrator') {

                if ($penalty_user) {
                    $left++;
                    $penalty_user->score -= $left_score;
                    $penalty_user->save();
                    if ($f->added_by)
                        $this->sendMessage($penalty_user->telegram_id, "🚨 متاسفانه به علت خروج ممبر اد شده توسط شما از  " . "$f->chat_username" . " تعداد " . " $left_score " . " سکه جریمه شدید ", 'MarkDown', null);

                    else
                        $this->sendMessage($penalty_user->telegram_id, "🚨 متاسفانه به علت خروج از  " . "$f->chat_username" . " تعداد " . " $left_score " . " سکه جریمه شدید ", 'MarkDown', null);
                }

                $f->left = true;
                $f->save();
            }

        }


        return $left;
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
