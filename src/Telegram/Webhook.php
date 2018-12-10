<?php

namespace Bus115\Telegram;

use Mockery\Exception;
use Longman\TelegramBot\Request;
use Silex\Application;

class Webhook
{

    const REQUESTS_LIMIT = 8;

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
//            \Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__ . "/../../data/logs/{$bot_username}_debug.log");
//            \Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__ . "/../../data/logs/{$bot_username}_update.log");
            \Longman\TelegramBot\TelegramLog::initialize($this->app['monolog']);

            $telegram->addCommandsPaths($commands_paths);
            // Requests Limiter (tries to prevent reaching Telegram API limits)
            $telegram->enableLimiter();

            // Enable admin
            $telegram->enableAdmin(intval($this->app['eway']['telegram_user_id']));

            // Setting App inside Telegram application
            $telegram->app = $this->app;

            $webhook_info_result = json_decode(Request::getWebhookInfo(), true)['result'];
            $webhook_info_title = '*Webhook Info:* ';

            if (isset($webhook_info_result['pending_update_count'])) {
                // Add a human-readable error date string if necessary.
                if (isset($webhook_info_result['last_error_date'])) {
                    $webhook_info_result['last_error_date_string'] = date('Y-m-d H:i:s', $webhook_info_result['last_error_date']);
                }
                $webhook_info_result_str = json_encode($webhook_info_result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                if((int)$webhook_info_result['pending_update_count'] > self::REQUESTS_LIMIT) {
                    $this->app['monolog']->info($webhook_info_title . $webhook_info_result_str);
                    return Request::emptyResponse();
                }
            }

            // Handle telegram webhook request
            $telegram->handle();
            sleep(1);

        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            $this->app['monolog']->info("TelegramException sleep 2");
            return Request::emptyResponse();
        } catch (\Longman\TelegramBot\Exception\TelegramLogException $e) {
            $this->app['monolog']->info("TelegramLogException sleep 2");
            return Request::emptyResponse();
        } catch (\Exception $e) {
            $this->app['monolog']->info("Exception sleep 2");
            return Request::emptyResponse();
        } catch (\Throwable $e) {
            $this->app['monolog']->info("Throwable sleep 2" . $e->getMessage());
            return Request::emptyResponse();
        }
    }

}
