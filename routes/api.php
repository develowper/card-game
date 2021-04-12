<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
//    Route::get('/getUser', function (Request $request) {
//        return $request->user();
//    });
//    Route::get('getUser', 'ApiController@getUser');
//    Route::post('logout', 'ApiController@logout');

    //schools
//    Route::post('schools/search', 'SchoolController@search')->name('school.search')->middleware('can:viewAny,App\School');
    Route::post('addtodivar', 'AppController@addToDivar');
    Route::get('getsettings', 'AppController@getSettings');
    Route::get('getdivar', 'AppController@getDivar');
    Route::get('getuser', 'AppController@getUser');
    Route::post('logout', 'APIController@logout');
    Route::post('checkuserjoined', 'AppController@checkuserJoined');
    Route::post('viewchat', 'AppController@viewChat');
    Route::post('newchat', 'AppController@newChat');
    Route::get('getuserchats', 'AppController@getUserChats');
    Route::post('refreshchat', 'AppController@refreshChat');
    Route::post('updatescore', 'AppController@updateScore');
    Route::post('penalty', 'AppController@leftUsersPenalty');
    Route::post('deletechat', 'AppController@deleteChat');

//simple version
    Route::post('simple/newchat', 'AppControllerSimple@newChat');
    Route::post('simple/addtovip', 'AppControllerSimple@addToVIP');
    Route::get('simple/getuserchats', 'AppControllerSimple@getUserChats');
    Route::get('simple/getdivar', 'AppControllerSimple@getDivar');
    Route::get('simple/getuserchats', 'AppControllerSimple@getUserChats');


});
Route::post('testmode', 'AppController@testMode');
Route::post('senderror', 'AppController@sendError');

Route::post('/bot/getupdates', 'BotController@getupdates');
Route::post('/bot/sendmessage', 'BotController@sendmessage');
Route::get('/bot/getme', 'BotController@myInfo');


Route::get('/getuserinchat', 'AppController@getUserInChat');
Route::get('/getme', 'AppController@myInfo');
Route::get('/getusers', 'AppController@getUsers');


Route::post('login', 'APIController@login');
Route::post('register', 'APIController@register');
