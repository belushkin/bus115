<?php

namespace Bus115\Telegram;

use Mockery\Exception;
use Longman\TelegramBot\Request;
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

        $commands_paths = [
            __DIR__ . '/Commands/',
        ];

        try {
            // Create Telegram API object
            $telegram = new \Longman\TelegramBot\Telegram($bot_api_key, $bot_username);

            //https://github.com/php-telegram-bot/core#example-bot
//            $dbParams = include ROOT_FOLDER . '/migrations-db.php';
//            $mysql_credentials = [
//                'host'     => $dbParams['host'],
//                'user'     => $dbParams['user'],
//                'password' => $dbParams['password'],
//                'database' => $dbParams['dbname'],
//            ];
//            $telegram->enableMySql($mysql_credentials);

            // Logging
            \Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__ . "/../../data/logs/{$bot_username}_error.log");
            \Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/../../data/logs/{$bot_username}_debug.log");
            \Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/../../data/logs/{$bot_username}_update.log");
            \Longman\TelegramBot\TelegramLog::initialize($this->app['monolog']);

            $telegram->addCommandsPaths($commands_paths);
            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter();

            // Setting App inside Telegram application
            $telegram->app = $this->app;

            // Handle telegram webhook request
            $telegram->handle();
            sleep(1);

        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            sleep(2);
            $this->app['monolog']->info("TelegramException sleep 2");
            $this->app['monolog']->info($e->getMessage());
        } catch (\Longman\TelegramBot\Exception\TelegramLogException $e) {
            sleep(2);
            $this->app['monolog']->info("TelegramLogException sleep 2");
            $this->app['monolog']->info($e->getMessage());
        } catch (\Exception $e) {
            sleep(2);
            $this->app['monolog']->info("Exception sleep 2");
        }
    }

}
