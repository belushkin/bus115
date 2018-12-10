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

        $text = 'оновити';
        if (!empty($callback_query->getMessage()->getVenue())) {
            $text = $callback_query->getMessage()->getVenue()->getTitle();
        }
        //$callback_query->getMessage()->getMessageId();
        $data = [
            'callback_query_id' => $callback_query_id,
            'text'              => $text,
            'cache_time'        => 5,
        ];
        Request::answerCallbackQuery($data);

        $params         = explode('_', $callback_data);
        $callbackObject = $this->telegram->app['app.telegram.transports']
            ->setMessage($callback_query->getMessage());

        if (count($params)) {
            return $callbackObject
                ->setEditMessageId(intval($params[0]))
                ->text(intval($params[1]));
        }

        return $callbackObject
            ->text(intval($callback_data));
    }
}
