<?php


namespace App\Service;

class Recaptcha
{
    protected $privateToken;
    
    public function __construct($privateToken){
        
        $this->privateToken = $privateToken;
        
    }
      public function isValid($code, $ip = null)
      {
        if(empty($code)) {
            return false;
        }
        $params = [
            'secret'    => $this->privateToken,
            'response'  => $code
        ];
          
        if($ip){
            $params['remoteip'] = $ip;
        }
          
        $url = "https://www.google.com/recaptcha/api/siteverify?" . http_build_query($params);
          
        if(function_exists('curl_version')){
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($curl);
        }else{
            $response = file_get_contents($url);
        }
        if(empty($response) || is_null($response)){
            return false;
        }
        $json = json_decode($response);
        return $json->success;
    }  
        
}