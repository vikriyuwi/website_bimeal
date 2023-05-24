<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiRule extends Controller
{
    public function responsemessage($status,$message,$data,$code)
    {
        return Response::json([
            "status"=>$status,
            "message"=>$message,
            "data"=>$data
        ],$code);
    }
}
