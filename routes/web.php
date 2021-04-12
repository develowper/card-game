<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Logo;
use Illuminate\Support\Facades\Crypt;
use App\VSGame;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {

    foreach (Helper::$divar_scores as $key => $val)
        echo $key;
    return;
    foreach (\App\UserChat::get() as $uc) {
        $user_id = $uc->user_id;
        $info = creator('getChat',
            ['chat_id' => $uc->chat_id]

        );
        $user = \App\User::where('telegram_id', "$user_id")->first();
        $info = isset($info->result) ? $info->result : null;
        // return json_encode($info);
        if ($info)
            \App\Chat::create([
                'user_id' => $user->id,
                'user_telegram_id' => "$user_id",
                'chat_id' => "$info->id",
                'chat_type' => 'channel',
                'chat_username' => $info->username,
                'chat_title' => $info->title,
                'chat_description' => isset($info->description) ? $info->description : null,
            ]);
    }

    return base64_encode("");
    $user_id = 72534783;
    // return Crypt::encrypt($user_id);
    return base64_decode(base64_encode(72534783));

    $client = new \GuzzleHttp\Client();
    $res = creator('getUserProfilePhotos', [
        'user_id' => $user_id,

    ])->result->photos;
    // return json_encode($res);
    if (!isset($res) || count($res) == 0) return;
    $res = creator('getFile', [
        'file_id' => $res[0][count($res[0]) - 1]->file_id,

    ])->result->file_path;

    $image = "https://api.telegram.org/file/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $res;
    Storage::put("public/users/$user_id.jpg", $client->get($image)->getBody());
});


Auth::routes();

//Route::get('/home', 'HomeController@index')->name('home');

function creator($method, $datas = [])
{
    $url = "https://api.telegram.org/bot" . env('TELEGRAM_BOT_TOKEN', 'YOUR-BOT-TOKEN') . "/" . $method;
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


