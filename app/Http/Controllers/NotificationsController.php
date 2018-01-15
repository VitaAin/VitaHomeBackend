<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class NotificationsController extends Controller
{
    /**
     * NotificationController constructor.
     */
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->responseOk('OK', Auth::user()->notifications);
    }

    public function noticeReply()
    {
        $reply = array_filter(Auth::user()->notifications->toArray(), function ($notice) {
            return !empty($notice . data . comment);
        });
        return $this->responseOk('OK', $reply);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function read()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return $this->responseSuccess('OK');
    }
}
