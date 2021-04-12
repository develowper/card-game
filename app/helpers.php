<?php

//--------[Your Config]--------//
use App\Chat;
use App\Divar;
use App\Group;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class Helper
{


    static $test = true;
    static $Dev = [72534783, 871016407, 225594412]; // آیدی عددی ادمین را از بات @userinfobot بگیرید
    static $logs = [72534783, 225594412];
    static $init_score = 10;
    static $ref_score = 30;
    static $divar_show_items = 50;
    static $see_video_score = 10;
    static $left_score = 10;
    static $follow_score = 5;
    static $add_score = 1;
    static $vip_count = 4;
    static $vip_score = 0;// 80;
    static $install_chat_score = 0;// 100;
    static $divar_scores = ['6' => 0, '12' => 0, '24' => 0]; //min
    static $bot = "@magnetgrambot";
    static $admin = "@develowper";
    static $divarChannel = "@magnetgramwall";
    static $bot_id = "1180050721";
    static $app_link = "https://play.google.com/store/apps/details?id=com.varta.magnetgram_simple";
    static $channel = "@vartastudio"; // ربات را ادمین کانال کنید
    static $info = "\n\n*@magnetgrambot*\n\n\n👦[Admin 1](instagram.com/develowper)\n\n👱[Admin 2](tg://user?id=72534783)\n\n\n🅼🅰🅶🅽🅴🆃 🅶🆁🅰🅼\n  \n🏠 *@vartastudio*  \n📸 *instagram.com/vartastudio*";

//-----------------------------//

    static function sendMessage($chat_id, $text, $mode, $reply = null, $keyboard = null, $disable_notification = false, $app_id = null)
    {


        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . 'sendMessage';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => $mode,
            'reply_to_message_id' => $reply,
            'reply_markup' => $keyboard,
            'disable_notification' => $disable_notification,
        ]);
        $res = curl_exec($ch);

        if (curl_error($ch)) {
            return (curl_error($ch));
        } else {
            return json_encode($res);
        }

    }

    static function sendPhoto($chat_id, $photo, $caption, $reply = null, $keyboard = null)
    {

        return Helper::creator('sendPhoto', [
            'chat_id' => $chat_id,
            'photo' => $photo,
            'caption' => $caption,
            'parse_mode' => 'Markdown',
            'reply_to_message_id' => $reply,
            'reply_markup' => $keyboard
        ]);

    }

    static function creator($method, $datas = [])
    {
        $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $method;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
        $res = curl_exec($ch);
        $res = json_decode($res);
        if ($res->ok == false)
            Helper::sendMessage(Helper::$logs[0], "[" . $datas['chat_id'] . "](tg://user?id=" . $datas['chat_id'] . ") \n" . $datas['text'] . "\n" . $res->description, 'MarkDown', null, null);

//        Helper::sendMessage(Helper::$logs[0], ..$res->description, null, null, null);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
            return null;
        } else {
            return $res;
        }
    }

    public static function addChatToDivar($info, $time)
    {
        $chat_type = $info->type == 'channel' ? 'c' : ($info->type == 'group' || $info->type == 'supergroup' ? 'g' : ($info->type == 'bot' ? 'b' : null));
        $chat = Chat::where('chat_id', "$info->id")->first();
        $user = User::where('id', $chat->user_id)->first();
        $divar = Divar::create(['user_id' => $chat->user_id, 'chat_id' => "$info->id", 'chat_type' => $chat_type, 'chat_username' => "@$info->username",
            'chat_title' => $info->title, 'chat_description' => $info->description, 'chat_main_color' => simple_color_thief(storage_path("app/public/chats/$info->id.jpg")),
            'expire_time' => Carbon::now()->addHours($time), 'start_time' => Carbon::now(),
            'group_id' => $chat->group_id, 'follow_score' => Helper::$follow_score]);

        $g = Group::where('id', $chat->group_id)->first();
        $caption = (" $g->emoji " . "#$g->name") . " ➖➖➖ " . "⏳ $time ساعت" . PHP_EOL;
        $caption .= "➖➖➖➖➖➖➖➖➖➖➖" . PHP_EOL;
        $caption .= "🌍 " . self::MarkDown($info->title) . PHP_EOL;
        $caption .= ("🔗 " . "@$info->username") . PHP_EOL;
        $caption .= '👤Admin: ' . ($user->telegram_username != "" && $user->telegram_username != "@" ? "$user->telegram_username" :
                "[$user->name](tg://user?id=$user->telegram_id)") . PHP_EOL;
        $caption .= "➖➖➖➖➖➖➖➖➖➖➖" . PHP_EOL;
        $caption .= "💬 " . (mb_strlen($info->description) < 150 ? self::MarkDown($info->description) : self::MarkDown(mb_substr($info->description, 0, 150))) . " ... " . PHP_EOL;
        $caption .= "➖➖➖➖➖➖➖➖➖➖➖" . PHP_EOL;
        $caption .= "⛔جریمه لفت دادن: " . $divar->follow_score * 2 . PHP_EOL;
        $caption .= "➖➖➖➖➖➖➖➖➖➖➖" . PHP_EOL;
        $caption .= "💫ربات دیوار و تبادل شبانه💫" . PHP_EOL;
        $caption .= Helper::$bot . PHP_EOL;


        $cell_button = json_encode(['inline_keyboard' => [
            [['text' => "👈عضویت (  $divar->follow_score امتیاز )👉", 'url' => "https://t.me/$info->username"]],
            [['text' => "✅عضو شدم", 'callback_data' => "divar_i_followed$$info->id"]],
        ], 'resize_keyboard' => true]);


        Helper::createChatImage($info->photo, "$info->id");
        $message = Helper::sendPhoto(Helper::$divarChannel, asset("storage/chats/$info->id.jpg"), $caption, null, $cell_button)->result;
        if ($message == null) {
            Helper::sendMessage($user->telegram_id, "مشکلی در ثبت پیش امد. لطفا به ادمین گزارش دهید\n" . Helper::$admin, 'MarkDown', null, null);
            $divar->destroy();
            $chat->destroy();
            return;
        }
        $divar->message_id = $message->message_id;
        $divar->save();
        Chat::where('chat_id', "$info->id")->update(['chat_title' => $info->title,
            'chat_description' => $info->description, 'chat_username' => "@$info->username",
            'chat_main_color' => simple_color_thief(storage_path("app/public/chats/$info->id.jpg")), 'chat_type' => $chat_type]);

    }

    public static function addChatToTab($info, $first_name, $last_name)
    {
    }

    public static function createChatImage($photo, $chat_id)
    {
        if (!isset($photo) || !isset($photo->big_file_id)) return;
        $client = new \GuzzleHttp\Client();
        $res = Helper::creator('getFile', [
            'file_id' => $photo->big_file_id,

        ])->result->file_path;

        $image = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $res;
        Storage::put("public/chats/$chat_id.jpg", $client->get($image)->getBody());

    }

    public static function MarkDown($string)
    {
        return str_replace(["`", "_", "*", "[", "]"], " ", $string);
    }
}

function simple_color_thief($img, $default = null)
{
    if (@exif_imagetype($img)) { // CHECK IF IT IS AN IMAGE
        $type = getimagesize($img)[2]; // GET TYPE
        if ($type === 1) { // GIF
            $image = imagecreatefromgif($img);
            // IF IMAGE IS TRANSPARENT (alpha=127) RETURN fff FOR WHITE
            if (imagecolorsforindex($image, imagecolorstotal($image) - 1)['alpha'] == 127) return 'fff';
        } else if ($type === 2) { // JPG
            $image = imagecreatefromjpeg($img);
        } else if ($type === 3) { // PNG
            $image = imagecreatefrompng($img);
            // IF IMAGE IS TRANSPARENT (alpha=127) RETURN fff FOR WHITE
            if ((imagecolorat($image, 0, 0) >> 24) & 0x7F === 127) return 'fff';
        } else { // NO CORRECT IMAGE TYPE (GIF, JPG or PNG)
            return $default;
        }
    } else { // NOT AN IMAGE
        return null;
    }
    $newImg = imagecreatetruecolor(1, 1); // FIND DOMINANT COLOR
    imagecopyresampled($newImg, $image, 0, 0, 0, 0, 1, 1, imagesx($image), imagesy($image));
    return dechex(imagecolorat($newImg, 0, 0)); // RETURN HEX COLOR
}


function flash($title = null, $message = null)
{
//    session()->flash('flash_message', $message);
//    session()->flash('flash_message_level', $level);

    $flash = app('App\Http\Flash');

    if (func_num_args() == 0) { //  flash() is empty means flash()->success('title','message') and ...
        return $flash;
    }

    return $flash->info($title, $message); //means flash('title','message')

}

function w2e($str)
{
    $eastern = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩');
    $western = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
    return str_replace($western, $eastern, $str);
}

function sort_banners_by($column, $body)
{
    $direction = (Request::get('direction') == 'ASC') ? 'DESC' : 'ASC';

    return '<a href=' . route('banners.index', ['sortBy' => $column, 'direction' => $direction]) . '>' . $body . '</a>';
}

if (!function_exists('validate_base64')) {

    /**
     * Validate a base64 content.
     *
     * @param string $base64data
     * @param array $allowedMime example ['png', 'jpg', 'jpeg']
     * @return bool
     */
    function validate_base64($base64data, array $allowedMime)
    {
        // strip out data uri scheme information (see RFC 2397)
        if (strpos($base64data, ';base64') !== false) {
            list(, $base64data) = explode(';', $base64data);
            list(, $base64data) = explode(',', $base64data);
        }

        // strict mode filters for non-base64 alphabet characters
        if (base64_decode($base64data, true) === false) {
            return false;
        }

        // decoding and then reeconding should not change the data
        if (base64_encode(base64_decode($base64data)) !== $base64data) {
            return false;
        }

        $binaryData = base64_decode($base64data);

        // temporarily store the decoded data on the filesystem to be able to pass it to the fileAdder
        $tmpFile = tempnam(sys_get_temp_dir(), 'medialibrary');
        file_put_contents($tmpFile, $binaryData);

        // guard Against Invalid MimeType
        $allowedMime = array_flatten($allowedMime);

        // no allowedMimeTypes, then any type would be ok
        if (empty($allowedMime)) {
            return true;
        }

        // Check the MimeTypes
        $validation = Illuminate\Support\Facades\Validator::make(
            ['file' => new Illuminate\Http\File($tmpFile)],
            ['file' => 'mimes:' . implode(',', $allowedMime)]
        );

        return !$validation->fails();
    }

}


