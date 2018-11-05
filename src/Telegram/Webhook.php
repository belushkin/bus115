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

    public function handle($messageId, $chatId)
    {
        $bot_api_key  = $this->app['eway']['telegram_token'];
        $bot_username = 'Bus115Bot';

        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

            \Longman\TelegramBot\TelegramLog::initialize($this->app['monolog']);

            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter();

            // Handle telegram webhook request
            $telegram->handle();
            $this->app['monolog']->info($telegram->getCustomInput());

        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // Silence is golden!
            // log telegram errors
            $this->app['monolog']->info($e->getMessage());
        }
    }

}
