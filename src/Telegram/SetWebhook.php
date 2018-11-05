<?php

namespace Bus115\Telegram;

use Silex\Application;

class SetWebhook
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
        $hook_url     = 'https://bus115.kiev.ua/api/v1/telegramwebhook';

        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

            // Set webhook
            $result = $telegram->setWebhook($hook_url);
            if ($result->isOk()) {
                return $result->getDescription();
            }
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            return $e->getMessage();
        }
        return true;
    }

}
