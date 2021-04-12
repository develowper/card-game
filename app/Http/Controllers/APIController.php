<?php

namespace App\Http\Controllers;

use App\Divar;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class APIController extends Controller
{
    public $successStatus = 200;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $input = $request->all();
        $input['password'] = Hash::make($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('AppName')->accessToken;
        return response()->json(['success' => $success], $this->successStatus);
    }


    public function login(Request $request)
    {

        $http = new
        \GuzzleHttp\Client(['base_uri' => 'http://localhost:81/_laravelProjects/magnetgram/public/',
        ]);

        try {
            $response = $http->post(/*route('passport.token'*/
                'oauth/token'
                /* 'http://localhost:81/_laravelProjects/ashayer/public/oauth/token'*/, [

                'headers' => ['cache-control' => 'no-cache',
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => config('services.passport.client_id'),
                    'client_secret' => config('services.passport.client_secret'),
                    'password' => $request->password,
                    'username' => "@" . str_replace("@", "", $request->username),
                ]
            ]);

            return $response->getBody();
        } catch (\Guzzlehttp\Exception\BadResponseException $e) {
            if ($e->getCode() == 400) {
                return response()->json('LOGIN_FAIL');
            } else if ($e->getCode() == 401) {
                return response()->json('LOGIN_FAIL');
            }
            return response()->json('SERVER_ERROR', $e->getCode());

        }
    }

    public function refreshToken()
    {
        $http = new \GuzzleHttp\Client(['base_uri' => 'http://localhost:81/_laravelProjects/ashayer/public/',
        ]);

        $response = $http->post('oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => 'the-refresh-token',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'scope' => '',
            ],
        ]);

        return json_decode((string)$response->getBody(), true); //return new token and refresh token
    }

    public function getUser()
    {
        $user = Auth::user();
        return response()->json(['success' => $user], $this->successStatus);
    }

    public function logout()
    {
        if (!auth()->user())
            return response()->json('کاربر وجود ندارد', 400);

        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });
//        auth()->guard()->logout();
        return response()->json(['message' => 'با موفقیت خارج شدید', 'status' => 200]);
    }
}
