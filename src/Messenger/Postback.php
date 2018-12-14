<?php

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
        $payload = htmlspecialchars(addslashes(trim(mb_strtolower($receivedPostback['payload']))));

        if ($payload === 'first hand shake') {
            $responses = $this->app['app.first_hand_shake']->text();
        } else {
            $responses = $this->app['app.transports']->text($payload);
        }

        $i = 0;
        foreach ($responses as $response) {
            $this->app['app.api']->callSendAPI($senderPsid, $response);
            $i++;
            if ($i > 2) {
                break;
            }
        }
    }

}
