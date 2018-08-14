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
        $intents    = (isset($nlp['entities']['intent']))   ? $nlp['entities']['intent']    : [];
        $address    = (isset($nlp['entities']['address']))  ? $nlp['entities']['address']   : [];

//        $this->app['monolog']->info(var_export($nlp, true));
//        $this->app['monolog']->info(var_export($nlp['entities']['intent'], true));

        if (isset($receivedMessage['text'])) {
            $text = htmlspecialchars(addslashes(trim($receivedMessage['text'])));
            if (empty($intents) && empty($address)) {
                if ($text == 'help') {
                    $responses = $this->app['app.help']->text($text);
                } else {
                    $responses = $this->app['app.regular_text']->text($text);
                }
            } else if (!empty($intents)){
                foreach ($intents as $intent) {
                    if ($intent['value'] == 'joke' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.joke']->text($text);
                    } else if ($intent['value'] == 'location_ask' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.location']->text($text);
                    } else if ($intent['value'] == 'first_hand_shake' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.first_hand_shake']->text($text);
                    } else if ($intent['value'] == 'location' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.regular_text']->text(
                            $this->app['app.trim_helper']->trim($text)
                        );
                    } else {
                        $responses = $this->app['app.fallback']->text($text);
                    }
                }
            } else if (!empty($address)) {
                foreach ($address as $item) {
                    $responses = $this->app['app.regular_text']->text($item['value']['value']);
                }
            }
        } else if (isset($receivedMessage['attachments'])) {
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $responses = $this->app['app.stops']->text($attachment);
                } else {
                    $responses = $this->app['app.image']->text($attachment);
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
