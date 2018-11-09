<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

class CallbackqueryCommand extends SystemCommand
{
    protected $name = 'callbackquery';
    protected $description = 'Reply to callback query';
    protected $version = '1.1.1';

    public function execute()
    {
        $callback_query    = $this->getCallbackQuery();
        $callback_query_id = $callback_query->getId();
        $callback_data     = $callback_query->getData();

        $data = [
            'callback_query_id' => $callback_query_id,
            'text'              => $callback_query->getMessage()->getVenue()->getTitle(),
            'cache_time'        => 5,
        ];

        Request::answerCallbackQuery($data);

        $params = explode('_', $callback_data);
        if ((isset($params[0]) && $params[0] == 'stop') && (isset($params[1]) && intval($params[1]) != 0)) {
            return $this->telegram->app['app.telegram.transports']->
            setMessage($callback_query->getMessage())->
            text(intval($params[1]));
        }

        $data = [
            'chat_id' => $callback_query->getMessage()->getChat()->getId(),
            'text'    => 'Маршрутів не знайдено, для допомоги надрукуй /help',
        ];
        return Request::sendMessage($data);
    }
}
