<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ApiRule extends Controller
{
    public function responsemessage($message,$data,$code)
    {
        $scode = "OK";
        switch ($code) {
            case 200:
                $scode = "OK";
                break;
            case 201:
                $scode = "Created";
                break;
            case 202:
                $scode = "Accepted";
                break;
            case 404:
                $scode = "Not Found";
                break;
            case 422:
                $scode = "Unprocessable Entity";
                break;
            case 500:
                $scode = "Internal Server Error";
                break;
            default:
                $scode = "Unknown";
                break;
        }

        return Response::json([
            "status"=>$scode,
            "message"=>$message,
            "data"=>$data
        ],$code);
    }
}
