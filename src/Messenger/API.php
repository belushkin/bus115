<?php

namespace Bus115\Messenger;

use Silex\Application;

class API
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function callSendAPI($senderPsid, $response)
    {
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token=' . $this->app['eway']['page_access_token'];

        $requestBody = [
            'recipient' => [
                'id' => $senderPsid
            ],
            'message' => $response
        ];

        $ch = curl_init($url);

        if ($requestBody['message']['text']) {
            $this->app['monolog']->info(sprintf('Sent message back: %s', $response['text']));
        } else {
            $this->app['monolog']->info(sprintf('Sent attachment back, %s', \GuzzleHttp\json_encode($requestBody)));
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \GuzzleHttp\json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_exec($ch);
    }

    public function getGoogleCoordinates($string)
    {
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($string)."&key=".$this->app['eway']['maps_key'];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        return \GuzzleHttp\json_decode($result);
    }

    public function witai($string)
    {
        $url = "https://api.wit.ai/message?q=".urlencode($string);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->app['eway']['wit_server_token']));

        $result = curl_exec($ch);
        return \GuzzleHttp\json_decode($result);
    }

}
