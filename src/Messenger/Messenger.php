<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

use GuzzleHttp\Client;
use Silex\Application;

class Messenger
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // Handles messages events
    public function handleMessage($senderPsid, $receivedMessage)
    {
        $response = [];

        // Check if the message contains text
        if (isset($receivedMessage['text'])) {
            // Create the payload for a basic text message
            $response = [
              'text' => "You sent the message: '{$receivedMessage['text']}'. Now send me an image!"
            ];
        }

        // Sends the response message
        $this->callSendAPI($senderPsid, $response);
    }

    // Handles messaging_postbacks events
    public function handlePostback($senderPsid, $receivedPostback)
    {

    }

    // Sends response messages via the Send API
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

        $this->app['monolog']->info(sprintf('Send message: %s', $requestBody['message']['text']));

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \GuzzleHttp\json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_exec($ch);
    }

}
