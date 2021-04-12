<?php
// https://creator.com
error_reporting(0);
set_time_limit(-1);
header("HTTP/1.0 200 OK");
date_default_timezone_set('Asia/Tehran');
//--------[Your Config]--------//
$Dev = [72534783]; // آیدی عددی ادمین را از بات @userinfobot بگیرید
$logs = -1001220562710;
$channel = "@salladbot"; // ربات را ادمین کانال کنید
//-----------------------------//
define('API_KEY', env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN')); // توکن ربات
//------------------------------------------------------------------------------
function creator($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
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

//------------------------------------------------------------------------------
function MarkDown($string)
{
    return str_replace(["`", "_", "*", "[", "]"], "\t", $string);
}

function pause($second)
{
    header("HTTP/1.1 200 OK");
    http_response_code(201);
    ob_implicit_flush(true);
    $start = microtime(true);
    for ($i = 1; $i <= $second; $i++) {
        @time_sleep_until($start + $i);
    }
    ob_flush();
}

//------------------------------------------------------------------------------
function SendMessage($chat_id, $text, $mode, $reply = null, $keyboard = null)
{
    return creator('SendMessage', [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => $mode,
        'reply_to_message_id' => $reply,
        'reply_markup' => $keyboard
    ]);
}

function EditMessageText($chat_id, $message_id, $text, $mode = null, $keyboard = null)
{
    creator('EditMessageText', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => $mode,
        'reply_markup' => $keyboard
    ]);
}

function EditKeyboard($chat_id, $message_id, $keyboard)
{
    creator('EditMessageReplyMarkup', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'reply_markup' => $keyboard
    ]);
}

function Forward($chatid, $from_id, $massege_id)
{
    creator('ForwardMessage', [
        'chat_id' => $chatid,
        'from_chat_id' => $from_id,
        'message_id' => $massege_id
    ]);
}

function DeleteMessage($chatid, $massege_id)
{
    creator('DeleteMessage', [
        'chat_id' => $chatid,
        'message_id' => $massege_id
    ]);
}

function Kick($chatid, $fromid)
{
    creator('KickChatMember', [
        'chat_id' => $chatid,
        'user_id' => $fromid
    ]);
}

function Admin($chat_id, $from_id)
{
    global $Dev;
    $get = creator('GetChatMember', ['chat_id' => $chat_id, 'user_id' => $from_id]);
    $rank = $get->result->status;
    if ($rank == 'creator' || $rank == 'administrator' || in_array($from_id, $Dev)) {
        return true;
    }
}

//------------------------------------------------------------------------------
function getUpdates()
{
    $update = json_decode(file_get_contents('php://input'));
    if (isset($update->message)) {
        $message = $update->message;
        $chat_id = $message->chat->id;
        $text = $message->text;
        $message_id = $message->message_id;
        $from_id = $message->from->id;
        $tc = $message->chat->type;
        $title = $message->chat->title;
        $first_name = $message->from->first_name;
        $last_name = $message->from->last_name;
        $username = $message->from->username;
        $caption = $message->caption;
        $reply = $message->reply_to_message->forward_from->id;
        $reply_id = $message->reply_to_message->from->id;
    }
    if (isset($update->callback_query)) {
        $Data = $update->callback_query->data;
        $data_id = $update->callback_query->id;
        $chat_id = $update->callback_query->message->chat->id;
        $from_id = $update->callback_query->from->id;
        $first_name = $update->callback_query->from->first_name;
        $last_name = $update->callback_query->from->last_name;
        $username = $update->callback_query->from->username;
        $tc = $update->callback_query->message->chat->type;
        $message_id = $update->callback_query->message->message_id;
    }
//------------------------------------------------------------------------------
    $get = json_decode(file_get_contents("https://api.telegram.org/bot" . API_KEY . "/getChatMember?chat_id=$this->channel&user_id=$from_id"), true);
    $rank = $get['result']['status'];
//------------------------------------------------------------------------------
    @$data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
    @$list = json_decode(file_get_contents("data/list.json"), true);
//------------------------------------------------------------------------------
    if ($tc == 'private') {
        if (preg_match('/^\/(start)$/i', $text)) {
            $button = json_encode(['keyboard' => [
                [['text' => "🥒 نصب رایگان"]],
                [['text' => "🤖 درباره ربات"], ['text' => "🔐 امکانات"]]
            ], 'resize_keyboard' => true]);
            SendMessage($chat_id, "■ سلام $first_name خوش آمدید\n\n■ لطفا یکی از گزینه ها را انتخاب کنید :", null, $message_id, $button);
            $first_name = MarkDown($first_name);
            SendMessage($this->logs, "■  کاربر [$first_name](tg://user?id=$from_id) ربات ادد بزن را استارت کرد.", 'MarkDown');
        } elseif ($rank == 'left') {
            SendMessage($chat_id, "■ برای استفاده از ربات و همچنین حمایت از ما ابتدا وارد کانال\n● $this->channel\n■ سپس به ربات برگشته و /start را بزنید.", null, $message_id, json_encode(['KeyboardRemove' => [], 'remove_keyboard' => true]));
        } elseif ($text == "🥒 نصب رایگان") {
            $BotID = creator('GetMe', [])->result->username;
            SendMessage($chat_id, "■ راهنمای نصب ربات :\n\nابتدا از طریق لینک زیر ربات را در گروهتان اضافه کنید:\nTelegram.me/$BotID?startgroup=start\nسپس در گروه خود دستور 'نصب' را ارسال کنید.\nاکنون ربات را ادمین گروه کنید.\nربات با موفقیت نصب شد!\n\nبرای تنظیم کردن ربات عبارت 'پنل' را در گروهتان ارسال کنید.\n\n🌹 موفق باشید", null, $message_id);
        } elseif ($text == "🔐 امکانات") {
            SendMessage($chat_id, "■ از امکانات این ربات می توان به نکات زیر اشاره کرد :\n\n● حذف خودکار پیام های خود پس از زمان تنظیم شده توسط شما !\n● قابلیت معاف کردن و رفع معافیت کاربری برای ادد زدن توسط مدیران !\n● مشاهده دقیق کاربران ادد شده از زمانی که ربات ادد بزن نصب گردیده !\n● تنظیم حداقل ادد زدن کاربران برای فعالیت در گروه !\n● قابلیت مشاهده کاربران معاف شده بصورت منشن شده !", null, $message_id);
        } elseif ($text == "🤖 درباره ربات") {
            SendMessage($chat_id, "😎 ربات افزودن اجباری 'ادد بزن' یکی از بهترین ربات ها در ضمینه عضو گیری اجباری در سطح تلگرام می باشد.\n\n👈🏻 از نکات مثبت این ربات می توان به (سرعت بالا - دقت بالا - پاسخ های فارسی - قابل تنظیم بودن حذف پیام های اخطار) اشاره کرد.", 'MarkDown', $message_id);
        }
    }
    elseif ($tc == 'supergroup') {
        $status = Admin($chat_id, $from_id);
        $addlist = json_decode(file_get_contents("data/$chat_id/addlist.json"), true);
        if ($status != true and $data['setting']['stats'] == "| ✅ فعال |" and isset($message) and !$message->new_chat_member->id) {
            if (!in_array($from_id, $data['whitelist'])) {
                if ($addlist[$from_id]['invite'] < $data['setting']['invite']) {
                    DeleteMessage($chat_id, $message_id);
                    $hours = getdate()['hours'];
                    if ($data['setting']['warn'] == "| ✅ فعال |" and $addlist[$from_id]['time'] != $hours) {
                        $addlist[$from_id]['time'] = $hours;
                        file_put_contents("data/$chat_id/addlist.json", json_encode($addlist));
                        $name = MarkDown($first_name);
                        $add = $data['setting']['invite'] - $addlist[$from_id]['invite'];
                        $send = SendMessage($chat_id, "• کاربر [$name](tg://user?id=$from_id) برای فعالیت در گروه ابتدا باید *$add* نفر دعوت کنید.", 'MarkDown');
                        if ($data['setting']['autodelete'] == "| ✅ فعال |") {
                            $database = json_decode(file_get_contents("data/database.json"), true);
                            $database[$chat_id][] = $send->result->message_id;
                            file_put_contents("data/database.json", json_encode($database));
                        }
                    }
                }
            }
        } elseif (isset($message->new_chat_member->id) and $status != true) {
            if (!$message->new_chat_member->is_bot) {
                $addlist[$from_id]['invite']++;
                file_put_contents("data/$chat_id/addlist.json", json_encode($addlist));
                $data['setting']['alladd']++;
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
            } else {
                if ($data['setting']['removebot'] == "| ✅ فعال |") {
                    Kick($chat_id, $message->new_chat_member->id);
                }
            }
        }
        if (preg_match('/^\/?(add|نصب)$/ui', $text, $match) and $status == true) {
            if (!is_dir("data/$chat_id")) {
                mkdir("data/$chat_id");
                $data = ['setting' => ['stats' => '| ✅ فعال |', 'alladd' => 0, 'invite' => 3, 'warn' => '| ✅ فعال |', 'autodelete' => '| ❌ غیرفعال |', 'timetodel' => 10, 'removebot' => '| ✅ فعال |'], 'whitelist' => []];
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $button = json_encode(['inline_keyboard' => [
                    [['text' => "◽️ رفتن به پنل", 'callback_data' => "panel"]]
                ]]);
                SendMessage($chat_id, "■ گروه [$title] با موفقیت نصب گردید.\n● اکنون باید من را در گروه ادمین کنید.\n● برای مشاهده نحوه کار با ربات دستور 'راهنما' را ارسال کنید.", null, $message_id, $button);
                $first_name = MarkDown($first_name);
                SendMessage($this->logs, "■ گروه \[$title] توسط [$first_name](tg://user?id=$from_id) نصب گردید.", 'MarkDown');
            } else {
                SendMessage($chat_id, "■ این گروه از قبل نصب بود !\n\n● برای مشاهده نحوه کار با ربات دستور 'راهنما' را ارسال کنید.", null, $message_id, $this->button);
            }
        } elseif (preg_match('/^\/?(panel|پنل)$/ui', $text) and $status == true) {
            if (isset($data)) {
                $stats = $data['setting']['stats'];
                $alladd = $data['setting']['alladd'];
                $invite = $data['setting']['invite'];
                $membersgp = creator('getChatMembersCount', ['chat_id' => $chat_id])->result;

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $stats, 'callback_data' => "stats"], ['text' => "🤖 فعالیت ربات", 'callback_data' => "txt"]],
                    [['text' => $alladd, 'callback_data' => "txt"], ['text' => "❄️ کل دعوت ها", 'callback_data' => "txt"]],
                    [['text' => $membersgp, 'callback_data' => "txt"], ['text' => "👥 آمار گروه", 'callback_data' => "txt"]],
                    [['text' => "📍 حداقل دعوت", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-"], ['text' => $invite, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+"]],
                    [['text' => "🎖 لیست سفید", 'callback_data' => "whitelist"], ['text' => "⚙️ تنظیمات دیگر", 'callback_data' => "othersettings"]],
                    [['text' => "✖️ بستن پنل", 'callback_data' => "close"], ['text' => "📌 راهنما", 'callback_data' => "help"]]
                ]]);
                SendMessage($chat_id, "■ برای مدیریت تنظیمات از کلید های زیر استفاده کنید :", null, $message_id, $button);
            }
        } elseif (preg_match('/^\/?(help|راهنما)$/ui', $text) and $status == true) {
            if (isset($data)) {
                SendMessage($chat_id, "📯 راهنمای ربات ادد بزن به شرح ذیل می باشد :\n\n*●* نصب  |  add/\n*-|* نصب ربات در گروه شما\n\n*●* پنل  |  panel/\n*-|* مشاهده پنل تنظیمات ربات در گروه شما\n\n*●* معاف  |  up/  (ریپلی | آیدی عددی)\n*-|* معاف کردن یک کاربر از ادد کردن اجباری\n\n*●* لغو معاف  |  unup/  (ریپلی | آیدی عددی)\n*-|* لغو معافیت یک کاربر از ادد کردن اجباری\n\n■ *Developer :* @creator\n■ *Out Channel :* @creator", 'MarkDown', $message_id);
            }
        } elseif (preg_match('/^\/?(up|معاف) (\d+)$/ui', $text, $match) and $status == true) {
            if (isset($data)) {
                if (!in_array($match[2], $data['whitelist'])) {
                    $data['whitelist'][] = $match[2];
                    file_put_contents("data/$chat_id/setting.json", json_encode($data));
                    $name = MarkDown(creator('getChat', ['chat_id' => $match[2]])->result->first_name);
                    SendMessage($chat_id, "■ کاربر \[[$name](tg://user?id=$match[2])] معاف شد.", 'MarkDown', $message_id);
                }
            }
        } elseif (preg_match('/^\/?(unup|لغو معاف)+ (\d+)$/ui', $text, $match) and $status == true) {
            if (isset($data)) {
                if (in_array($match[2], $data['whitelist'])) {
                    $search = array_search($match[2], $data['whitelist']);
                    unset($data['whitelist'][$search]);
                    $data['whitelist'] = array_values($data['whitelist']);
                    file_put_contents("data/$chat_id/setting.json", json_encode($data));
                    $name = MarkDown(creator('getChat', ['chat_id' => $match[2]])->result->first_name);
                    SendMessage($chat_id, "■ کاربر \[[$name](tg://user?id=$match[2])] لغو معاف شد.", 'MarkDown', $message_id);
                }
            }
        } elseif (preg_match('/^\/?(up|معاف)$/ui', $text) and $status == true) {
            if (isset($data) and isset($reply_id)) {
                if (!in_array($reply_id, $data['whitelist'])) {
                    $data['whitelist'][] = $reply_id;
                    file_put_contents("data/$chat_id/setting.json", json_encode($data));
                    $name = MarkDown(creator('getChat', ['chat_id' => $reply_id])->result->first_name);
                    SendMessage($chat_id, "■ کاربر \[[$name](tg://user?id=$reply_id)] معاف شد.", 'MarkDown', $message_id);
                }
            }
        } elseif (preg_match('/^\/?(unup|لغو معاف)$/ui', $text) and $status == true) {
            if (isset($data) and isset($reply_id)) {
                if (in_array($reply_id, $data['whitelist'])) {
                    $search = array_search($reply_id, $data['whitelist']);
                    unset($data['whitelist'][$search]);
                    $data['whitelist'] = array_values($data['whitelist']);
                    file_put_contents("data/$chat_id/setting.json", json_encode($data));
                    $name = MarkDown(creator('getChat', ['chat_id' => $reply_id])->result->first_name);
                    SendMessage($chat_id, "■ کاربر \[[$name](tg://user?id=$reply_id)] لغو معاف شد.", 'MarkDown', $message_id);
                }
            }
        } elseif ($Data == "panel") {
            if ($status == true) {
                $stats = $data['setting']['stats'];
                $alladd = $data['setting']['alladd'];
                $invite = $data['setting']['invite'];
                $membersgp = creator('getChatMembersCount', ['chat_id' => $chat_id])->result;

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $stats, 'callback_data' => "stats"], ['text' => "🤖 فعالیت ربات", 'callback_data' => "txt"]],
                    [['text' => $alladd, 'callback_data' => "txt"], ['text' => "❄️ کل دعوت ها", 'callback_data' => "txt"]],
                    [['text' => $membersgp, 'callback_data' => "txt"], ['text' => "👥 آمار گروه", 'callback_data' => "txt"]],
                    [['text' => "📍 حداقل دعوت", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-"], ['text' => $invite, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+"]],
                    [['text' => "🎖 لیست سفید", 'callback_data' => "whitelist"], ['text' => "⚙️ تنظیمات دیگر", 'callback_data' => "othersettings"]],
                    [['text' => "✖️ بستن پنل", 'callback_data' => "close"], ['text' => "📌 راهنما", 'callback_data' => "help"]]
                ]]);
                EditMessageText($chat_id, $message_id, "■ برای مدیریت تنظیمات از کلید های زیر استفاده کنید :", null, $button);
            }
        } elseif ($Data == "othersettings") {
            if ($status == true) {
                $warn = $data['setting']['warn'];
                $autodelete = $data['setting']['autodelete'];
                $timetodel = $data['setting']['timetodel'];
                $removebot = $data['setting']['removebot'];

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $warn, 'callback_data' => "warn"], ['text' => "❗️ ارسال اخطار", 'callback_data' => "txt"]],
                    [['text' => $autodelete, 'callback_data' => "autodelete"], ['text' => "🔄 حذف خودکار", 'callback_data' => "txt"]],
                    [['text' => $removebot, 'callback_data' => "removebot"], ['text' => "💉 قفل ورود ربات", 'callback_data' => "txt"]],
                    [['text' => "⏰ تایمر حذف", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-t"], ['text' => $timetodel, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+t"]],
                    [['text' => "🔙 برگشت", 'callback_data' => "panel"]]
                ]]);
                EditMessageText($chat_id, $message_id, "⚙️ به بخش تنظیمات مربوط به اخطار خوش آمدید\n\n■ برای مدیریت تنظیمات از کلید های زیر استفاده کنید :", null, $button);
            }
        } elseif ($Data == "warn") {
            if ($status == true) {
                if ($data['setting']['warn'] == "| ✅ فعال |") {
                    $data['setting']['warn'] = "| ❌ غیرفعال |";
                } else {
                    $data['setting']['warn'] = "| ✅ فعال |";
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $warn = $data['setting']['warn'];
                $autodelete = $data['setting']['autodelete'];
                $timetodel = $data['setting']['timetodel'];
                $removebot = $data['setting']['removebot'];

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $warn, 'callback_data' => "warn"], ['text' => "❗️ ارسال اخطار", 'callback_data' => "txt"]],
                    [['text' => $autodelete, 'callback_data' => "autodelete"], ['text' => "🔄 حذف خودکار", 'callback_data' => "txt"]],
                    [['text' => $removebot, 'callback_data' => "removebot"], ['text' => "💉 قفل ورود ربات", 'callback_data' => "txt"]],
                    [['text' => "⏰ تایمر حذف", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-t"], ['text' => $timetodel, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+t"]],
                    [['text' => "🔙 برگشت", 'callback_data' => "panel"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "autodelete") {
            if ($status == true) {
                if ($data['setting']['autodelete'] == "| ✅ فعال |") {
                    $data['setting']['autodelete'] = "| ❌ غیرفعال |";
                } else {
                    if ($data['setting']['warn'] == "| ✅ فعال |") {
                        $data['setting']['autodelete'] = "| ✅ فعال |";
                    }
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $warn = $data['setting']['warn'];
                $autodelete = $data['setting']['autodelete'];
                $timetodel = $data['setting']['timetodel'];
                $removebot = $data['setting']['removebot'];

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $warn, 'callback_data' => "warn"], ['text' => "❗️ ارسال اخطار", 'callback_data' => "txt"]],
                    [['text' => $autodelete, 'callback_data' => "autodelete"], ['text' => "🔄 حذف خودکار", 'callback_data' => "txt"]],
                    [['text' => $removebot, 'callback_data' => "removebot"], ['text' => "💉 قفل ورود ربات", 'callback_data' => "txt"]],
                    [['text' => "⏰ تایمر حذف", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-t"], ['text' => $timetodel, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+t"]],
                    [['text' => "🔙 برگشت", 'callback_data' => "panel"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "removebot") {
            if ($status == true) {
                if ($data['setting']['removebot'] == "| ✅ فعال |") {
                    $data['setting']['removebot'] = "| ❌ غیرفعال |";
                } else {
                    $data['setting']['removebot'] = "| ✅ فعال |";
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $warn = $data['setting']['warn'];
                $autodelete = $data['setting']['autodelete'];
                $timetodel = $data['setting']['timetodel'];
                $removebot = $data['setting']['removebot'];

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $warn, 'callback_data' => "warn"], ['text' => "❗️ ارسال اخطار", 'callback_data' => "txt"]],
                    [['text' => $autodelete, 'callback_data' => "autodelete"], ['text' => "🔄 حذف خودکار", 'callback_data' => "txt"]],
                    [['text' => $removebot, 'callback_data' => "removebot"], ['text' => "💉 قفل ورود ربات", 'callback_data' => "txt"]],
                    [['text' => "⏰ تایمر حذف", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-t"], ['text' => $timetodel, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+t"]],
                    [['text' => "🔙 برگشت", 'callback_data' => "panel"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "stats") {
            if ($status == true) {
                if ($data['setting']['stats'] == "| ✅ فعال |") {
                    $data['setting']['stats'] = "| ❌ غیرفعال |";
                } else {
                    $data['setting']['stats'] = "| ✅ فعال |";
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $stats = $data['setting']['stats'];
                $alladd = $data['setting']['alladd'];
                $invite = $data['setting']['invite'];
                $membersgp = creator('getChatMembersCount', ['chat_id' => $chat_id])->result;

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $stats, 'callback_data' => "stats"], ['text' => "🤖 فعالیت ربات", 'callback_data' => "txt"]],
                    [['text' => $alladd, 'callback_data' => "txt"], ['text' => "❄️ کل دعوت ها", 'callback_data' => "txt"]],
                    [['text' => $membersgp, 'callback_data' => "txt"], ['text' => "👥 آمار گروه", 'callback_data' => "txt"]],
                    [['text' => "📍 حداقل دعوت", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-"], ['text' => $invite, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+"]],
                    [['text' => "🎖 لیست سفید", 'callback_data' => "whitelist"], ['text' => "⚙️ تنظیمات دیگر", 'callback_data' => "othersettings"]],
                    [['text' => "✖️ بستن پنل", 'callback_data' => "close"], ['text' => "📌 راهنما", 'callback_data' => "help"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "+" || $Data == "-") {
            if ($status == true) {
                $invite = $data['setting']['invite'];
                switch ($Data) {
                    case "+":
                        if ($data['setting']['invite'] < 50) {
                            $data['setting']['invite'] = $invite + 1;
                        }
                        break;
                    case "-":
                        if ($data['setting']['invite'] > 1) {
                            $data['setting']['invite'] = $invite - 1;
                        }
                        break;
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $stats = $data['setting']['stats'];
                $alladd = $data['setting']['alladd'];
                $invite = $data['setting']['invite'];
                $membersgp = creator('getChatMembersCount', ['chat_id' => $chat_id])->result;

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $stats, 'callback_data' => "stats"], ['text' => "🤖 فعالیت ربات", 'callback_data' => "txt"]],
                    [['text' => $alladd, 'callback_data' => "txt"], ['text' => "❄️ کل دعوت ها", 'callback_data' => "txt"]],
                    [['text' => $membersgp, 'callback_data' => "txt"], ['text' => "👥 آمار گروه", 'callback_data' => "txt"]],
                    [['text' => "📍 حداقل دعوت", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-"], ['text' => $invite, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+"]],
                    [['text' => "🎖 لیست سفید", 'callback_data' => "whitelist"], ['text' => "⚙️ تنظیمات دیگر", 'callback_data' => "othersettings"]],
                    [['text' => "✖️ بستن پنل", 'callback_data' => "close"], ['text' => "📌 راهنما", 'callback_data' => "help"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "+t" || $Data == "-t") {
            if ($status == true) {
                $timetodel = $data['setting']['timetodel'];
                switch ($Data) {
                    case "+t":
                        if ($data['setting']['timetodel'] < 60) {
                            $data['setting']['timetodel'] = $timetodel + 5;
                        }
                        break;
                    case "-t":
                        if ($data['setting']['timetodel'] > 5) {
                            $data['setting']['timetodel'] = $timetodel - 5;
                        }
                        break;
                }
                file_put_contents("data/$chat_id/setting.json", json_encode($data));
                $data = json_decode(file_get_contents("data/$chat_id/setting.json"), true);
                $warn = $data['setting']['warn'];
                $autodelete = $data['setting']['autodelete'];
                $timetodel = $data['setting']['timetodel'];
                $removebot = $data['setting']['removebot'];

                $button = json_encode(['inline_keyboard' => [
                    [['text' => $warn, 'callback_data' => "warn"], ['text' => "❗️ ارسال اخطار", 'callback_data' => "txt"]],
                    [['text' => $autodelete, 'callback_data' => "autodelete"], ['text' => "🔄 حذف خودکار", 'callback_data' => "txt"]],
                    [['text' => $removebot, 'callback_data' => "removebot"], ['text' => "💉 قفل ورود ربات", 'callback_data' => "txt"]],
                    [['text' => "⏰ تایمر حذف", 'callback_data' => "txt"]],
                    [['text' => "➖", 'callback_data' => "-t"], ['text' => $timetodel, 'callback_data' => "txt"], ['text' => "➕", 'callback_data' => "+t"]],
                    [['text' => "🔙 برگشت", 'callback_data' => "panel"]]
                ]]);
                EditKeyboard($chat_id, $message_id, $button);
            }
        } elseif ($Data == "whitelist") {
            if ($status == true) {
                if (empty($data['whitelist']) === false) {
                    $list = $data['whitelist'];
                    $string = null;
                    foreach ($list as $value) {
                        $first_name = creator('getChat', ['chat_id' => $value])->result->first_name;
                        $name = MarkDown($first_name);
                        $string .= "[$name](tg://user?id=$value)" . PHP_EOL;
                    }
                    EditMessageText($chat_id, $message_id, "■ لیست سفید (افراد معاف) گروه :\n\n$string", 'MarkDown', json_encode(['inline_keyboard' => [[['text' => "🔙 برگشت", 'callback_data' => "panel"]]]]));
                } else {
                    creator('AnswerCallbackQuery', [
                        'callback_query_id' => $data_id,
                        'text' => "🎈 لیست سفید (افراد معاف) خالی است!",
                        'show_alert' => true
                    ]);
                }
            }
        } elseif ($Data == "help") {
            if ($status == true) {
                EditMessageText($chat_id, $message_id, "📯 راهنمای ربات ادد بزن به شرح ذیل می باشد :\n\n*●* نصب  |  add/\n*-|* نصب ربات در گروه شما\n\n*●* پنل  |  panel/\n*-|* مشاهده پنل تنظیمات ربات در گروه شما\n\n*●* معاف  |  up/  (ریپلی | آیدی عددی)\n*-|* معاف کردن یک کاربر از ادد کردن اجباری\n\n*●* لغو معاف  |  unup/  (ریپلی | آیدی عددی)\n*-|* لغو معافیت یک کاربر از ادد کردن اجباری\n\n■ *Developer :* @creator\n■ *Out Channel :* @creator", 'MarkDown', json_encode(['inline_keyboard' => [[['text' => "🔙 برگشت", 'callback_data' => "panel"]]]]));
            }
        } elseif ($Data == "close") {
            if ($status == true) {
                $name = MarkDown($first_name);
                EditMessageText($chat_id, $message_id, "■ پنل توسط [$name](tg://user?id=$from_id) بسته شد !", 'MarkDown');
            }
        }
    }
//------------------------------------------------------------------------------
    if (in_array($from_id, $this->Dev)) {
        $panel = json_encode(['keyboard' => [
            [['text' => "📊 آمار"]],
            [['text' => "📬 ارسال همگانی کاربران"], ['text' => "📮 فروارد همگانی کاربران"]],
            [['text' => "📬 ارسال همگانی گروه"], ['text' => "📮 فروارد همگانی گروه"]],
            [['text' => "▫️ برگشت ▫️"]]
        ], 'resize_keyboard' => true]);
        $backpanel = json_encode(['keyboard' => [
            [['text' => "▫️ برگشت به پنل ▫️"]]
        ], 'resize_keyboard' => true]);
        $step = file_get_contents("step");
        $list = json_decode(file_get_contents("data/list.json"), true);
        if (preg_match('/^\/(panel)$/i', $text) || $text == "▫️ برگشت به پنل ▫️") {
            $data[$from_id]['step'] = "none";
            file_put_contents("Data/data.json", json_encode($data));
            SendMessage($chat_id, "■ یکی از گزینه های زیر را انتخاب کنید :", null, $message_id, $panel);
        } elseif ($text == "📊 آمار") {
            $users = count(array_unique($list['user']));
            $groups = count(scandir("data")) - 4;

            $count = count($list['user']) - 9;
            $lastmem = null;
            foreach ($list['user'] as $key => $value) {
                if ($count <= $key) {
                    $lastmem .= "[$value](tg://user?id=$value) | ";
                    $key++;
                }
            }
            SendMessage($chat_id, "■ تعداد کاربران ربات : $users\n■ تعداد گروه های مدیریتی : $groups\n\n■ 9 کاربر اخیر ربات :\n$lastmem", 'MarkDown', $message_id);
        } elseif ($text == "📬 ارسال همگانی کاربران") {
            file_put_contents("step", "senduser");
            file_put_contents("Data/data.json", json_encode($data));
            SendMessage($chat_id, "■ پیام مورد نظر را ارسال کنید", 'MarkDown', $message_id, $backpanel);
        } elseif ($step == "senduser" and isset($text)) {
            unlink("step");
            foreach ($list['user'] as $id) {
                SendMessage($id, $text, null, null, null);
            }
            SendMessage($chat_id, "■ پیام به تمامی اعضا ارسال شد", null, null, $panel);
        } elseif ($text == "📮 فروارد همگانی کاربران") {
            file_put_contents("step", "fwduser");
            SendMessage($chat_id, "■ پیام مورد نظر را فروارد کنید", 'MarkDown', $message_id, $backpanel);
        } elseif ($step == "fwduser" and isset($message)) {
            unlink("step");
            foreach ($list['user'] as $id) {
                Forward($id, $chat_id, $message_id);
            }
            SendMessage($chat_id, "■ پیام به تمامی اعضا فروارد شد", null, null, $panel);
        } elseif ($text == "📬 ارسال همگانی گروه") {
            file_put_contents("step", "sendgp");
            file_put_contents("Data/data.json", json_encode($data));
            SendMessage($chat_id, "■ پیام مورد نظر را ارسال کنید", 'MarkDown', $message_id, $backpanel);
        } elseif ($step == "sendgp" and isset($text)) {
            unlink("step");
            $scan = scandir("data");
            $list['gp'] = array_diff($scan, [".", "..", "database.json", "list.json"]);
            foreach ($list['gp'] as $id) {
                SendMessage($id, $text, null, null, null);
            }
            SendMessage($chat_id, "■ پیام به تمامی گروه ها ارسال شد", null, null, $panel);
        } elseif ($text == "📮 فروارد همگانی گروه") {
            file_put_contents("step", "fwdgp");
            SendMessage($chat_id, "■ پیام مورد نظر را فروارد کنید", 'MarkDown', $message_id, $backpanel);
        } elseif ($step == "fwdgp" and isset($message)) {
            unlink("step");
            $scan = scandir("data");
            $list['gp'] = array_diff($scan, [".", "..", "database.json", "list.json"]);
            foreach ($list['gp'] as $id) {
                Forward($id, $chat_id, $message_id);
            }
            SendMessage($chat_id, "■ پیام به تمامی گروه ها فروارد شد", null, null, $panel);
        }
    }
//------------------------------------------------------------------------------
    if (in_array($from_id, $list['user']) == false and $from_id and $tc == 'private') {
        if ($list['user'] == null) {
            $list['user'] = [];
        }
        array_push($list['user'], $from_id);
        file_put_contents("data/list.json", json_encode($list, true));
    }
//------------------------------------------------------------------------------
    unlink("error_log");
}

?>