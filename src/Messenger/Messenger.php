<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

use Silex\Application;

class Messenger implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($senderPsid, $receivedMessage)
    {
        $responses = [];

        $this->app['monolog']->info(sprintf('Handle Message'));

        if (isset($receivedMessage['text'])) {
            $responses = $this->app['app.regular_text']->text($receivedMessage['text']);
        } else if (isset($receivedMessage['attachments'])) {
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $responses = $this->app['app.stops']->text($attachment);
                }
            }
        }

        foreach ($responses as $response) {
            $this->app['app.api']->callSendAPI($senderPsid, $response);
        }
    }

}
