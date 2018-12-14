<?php

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
        $location   = (isset($nlp['entities']['location'])) ? $nlp['entities']['location']  : [];

        // If user entered message
        if (isset($receivedMessage['text'])) {
            // Get this text and clean it
            $text = htmlspecialchars(addslashes(trim(mb_strtolower($receivedMessage['text']))));

            // Handle quick reply from the user
            if (strpos($receivedMessage['payload'], '__')) {
                $responses = $this->app['app.transports']->text($receivedMessage['payload']);
            } else if (empty($intents) && empty($address) && empty($location)) {
                // Check wit.ai intents
                // If nothing found from wit.ai forward it to regular text flow
                if ($text == 'help' || $text == 'допомога' || $text == 'помощь') {
                    $responses = $this->app['app.help']->text($text);
                } else {
                    $responses = $this->app['app.fallback']->text($text);
                }
            } else if (!empty($location)) {
                // If wit.ai decided that this is location
                foreach ($location as $item) {
                    $this->app['monolog']->info("WIT MESSENGER LOCATION VALUE" . $item['value']);

                    $responses = $this->app['app.location']->text($item['value']);
                    break;
                }
            } else if (!empty($address)) {
                // If wit.ai decided that this is address
                foreach ($address as $item) {
                    $this->app['monolog']->info("WIT MESSENGER ADDRESS VALUE" . $item['value']);

                    $responses = $this->app['app.address']->text($item['value']);
                    // Check for this specific false because the result can be different
                    if ($responses === false) {
                        $responses = $this->app['app.location']->text($item['value']);
                    }
                    break;
                }
            } else if (!empty($intents)){
                // Walking through the intents
                foreach ($intents as $intent) {
                    if ($intent['value'] == 'joke' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.joke']->text($text);
                    } else if ($intent['value'] == 'location_ask' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.ask_location']->text($text);
                    } else if ($intent['value'] == 'first_hand_shake' && $intent['confidence'] > self::NLP_THRESHOLD) {
                        $responses = $this->app['app.first_hand_shake']->text($text);
                    } else {
                        $responses = $this->app['app.fallback']->text($text);
                    }
                }
            } else {
                $responses = $this->app['app.fallback']->text($text);
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
