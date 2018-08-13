<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

use Silex\Application;

class Messenger implements MessageInterface
{

    const NLP_THRESHOLD = 0.8;

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($senderPsid, $receivedMessage, $nlp)
    {
        $responses  = [];
        $intents    = (isset($nlp['entities']['intent'])) ? $nlp['entities']['intent'] : [];

//        $this->app['monolog']->info(var_export($nlp, true));
//        $this->app['monolog']->info(var_export($nlp['entities']['intent'], true));

        if (isset($receivedMessage['text'])) {
            if (empty($intents)) {
                $responses = $this->app['app.regular_text']->text($receivedMessage['text']);
            } else {
                foreach ($intents as $intent) {
                    if ($intent['value'] == 'joke' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.joke']->text($receivedMessage['text']);
                    }
                }
            }
        } else if (isset($receivedMessage['attachments'])) {
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $responses = $this->app['app.stops']->text($attachment);
                }
            }
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
