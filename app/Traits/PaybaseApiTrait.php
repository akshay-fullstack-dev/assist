<?php

namespace App\Traits;

use Auth;
use Illuminate\Support\Facades\Request;
use App\User;
use Illuminate\Support\Facades\Log;

trait PaybaseApiTrait
{
    public function paybaseApi($body,$url,$method='post'){
        $client = new \GuzzleHttp\Client();

        $response = $client->request($method,$url,
            [
                "body" => json_encode($body),
                "headers" => [
                    "Content-Type" => "application/json",
                    "X-Token" => env('PAYBASE_TOKEN'),
                ]
            ]
        );
        return json_decode($response->getBody()->getContents());
    }

    public function paybaseExceptionErrorMessage($ex,$customMessage=''){
        if($ex->getCode() == 504){
            $message = '504 Gateway Time-out';
        }elseif($customMessage){
            $message = $customMessage;
        }else{
            $message = str_replace('"','',$this->get_string_between($ex->getMessage(), 'message":', ','));
        }
        return $message;
    }

    public function get_string_between($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }

}
