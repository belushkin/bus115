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
            'text'              => 'Hello World!',
            'show_alert'        => $callback_data === 'thumb up',
            'cache_time'        => 5,
        ];

        Request::answerCallbackQuery($data);
        $data = [
            'chat_id' => $this->getCallbackQuery()->getMessage()->getChat()->getId(),
            'text'    => 'Нічого не знайдено, для допомоги надрукуй /help',
        ];
        return Request::sendMessage($data);
    }
}
