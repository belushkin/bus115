<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

use Silex\Application;

class Postback implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($senderPsid, $receivedPostback, $nlp)
    {
        $responses = [];

        // Get the payload for the postback
        $payload = $receivedPostback['payload'];
        $this->app['monolog']->info(sprintf('Postback\'s payload: %s', $payload));

        if (intval($payload) != 0 && strpos($payload, '_') === false) { // show list of stops
            $responses = $this->app['app.transports']->text($payload);
        } else if (strpos($payload, '_')) { // show specific arrival time for the transport
            $responses = $this->app['app.arrival_message']->text($payload);
        } else if ($payload === 'first hand shake') {
            $responses = $this->app['app.first_hand_shake']->text();
        }

        $i = 0;
        foreach ($responses as $response) {
            $this->app['app.api']->callSendAPI($senderPsid, $response);
            $i++;
            if ($i > 1) {
                break;
            }
        }
    }

}
