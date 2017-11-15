<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
    * 错误码，1为正常
    * @var int
    */
    protected $status = 1;

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function response($message, $data = null)
    {
        return response()->json([
            "status" => $this->getStatus(),
            "message" => $message,
            "data" => $data
        ]);
    }

    public function responseOk($message, $data = null)
    {
        return $this->response($message, $data);
    }

    public function responseError($message, $data = null)
    {
        return $this->setStatus(0)->response($message, $data);
    }
}
