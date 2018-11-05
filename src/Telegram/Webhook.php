<?php

namespace Bus115\Telegram;

use Silex\Application;

class Webhook
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle()
    {
        $bot_api_key  = $this->app['eway']['telegram_token'];
        $bot_username = 'Bus115Bot';

        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

            // Handle telegram webhook request
            $telegram->handle();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // Silence is golden!
            // log telegram errors
            // echo $e->getMessage();
        }
    }

}
