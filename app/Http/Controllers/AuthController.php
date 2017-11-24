<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Validator;
use Illuminate\Support\Carbon;
use Mail;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth', [
            'only' => [/*'login', */
                'logout']
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users|between:4,20',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'password_confirmation' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->responseError('Validate failed', $validator->errors()->toArray());
        }

        $newUser = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'avatar' => env('APP_URL') . '/image/avatar.jpeg',
            'confirm_code' => str_random(60)
        ];

        $user = User::create($newUser);
        $this->sendVerifyEmailTo($user);
//        $user->attachRole(3);

        return $this->responseOk('Register successfully');
    }

    private function sendVerifyEmailTo($user)
    {
        $bind_data = [
            'name' => $user->name,
            'verify_url' => 'http://vitain.top/#/verify_email' . $user->conform_code];
        $template = new SendCloudTemplate('vitahome_register_verify', $bind_data);

        Mail::raw($template, function ($message) use ($user) {
            $message->from('service@vitain.top', 'VitaHome注册验证');
            $message->to($user->email);
        });

//        $data = [ 'url' => 'https://laravue.org/#/verify_email/' . $user->confirm_code,
//            'name' => $user->name ];
//        $template = new SendCloudTemplate('laravue_verify', $data);
//
//        Mail::raw($template, function ($message) use ($user) {
//            $message->from('root@laravue.org', 'laravue.org');
//            $message->to($user->email);
//        });
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return $this->responseError('Validate failed', $validator->errors()->toArray());
        }

        $loginBy = filter_var($request->get('account'), FILTER_VALIDATE_EMAIL) ? 'email' : 'name';
        $data = array_merge([
            $loginBy => $request->get('account'),
            'password' => $request->get('password')
        ]);

        try {
            $token = JWTAuth::attempt($data);
            if (!$token) {
                return $this->responseError('Username or password errors');
            }
            $user = Auth::user();
            // TODO release after confirmed by email
//            if ($user->is_confirmed == 0) {
//                return $this->responseError('Not activated');
//            }
            $user->jwt_token = [
                'access_token' => $token,
                'expires_in' => Carbon::now()->addMinutes(config('jwt.ttl'))->timestamp
            ];
            return $this->responseOk('Login successfully', $user->toArray());
        } catch (JWTException $e) {
            return $this->responseError('Cannot create token');
        }
    }

    public function logout()
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (TokenBlacklistedException $e) {
            return $this->responseError('Token is in black-list');
        } catch (JWTException $e) {
            // 忽略该异常（Authorization为空时会发生）
        }
        return $this->responseOk('Logout successfully');
    }
}
