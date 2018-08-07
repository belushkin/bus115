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
}
