<?php

namespace App\Http\Controllers;

use function GuzzleHttp\Promise\all;
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
        $allNotice = Auth::user()->notifications->toArray();
        $reply = array_map(function ($notice) {
            if ($notice['type'] == 'App\Notifications\CommentArticleNotification') {
                return $notice;
            }
        }, $allNotice);
        return $this->responseOk('OK', $reply);
    }

    public function noticeFollow()
    {
        $allNotice = Auth::user()->notifications->toArray();
        $follow = array_filter($allNotice, function ($notice) {
            return $notice['type'] == 'App\Notifications\FollowUserNotification';
        });
        return $this->responseOk('OK', $follow);
    }

    public function noticeLike()
    {
        $allNotice = Auth::user()->notifications->toArray();
        $reply = array_filter($allNotice, function ($notice) {
            return $notice['type'] == 'App\Notifications\LikeArticleNotification';
        });
        return $this->responseOk('OK', $reply);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function read()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return $this->responseOk('OK');
    }
}
