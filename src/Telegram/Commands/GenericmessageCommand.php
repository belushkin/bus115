<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

class GenericmessageCommand extends SystemCommand
{
    protected $name = 'Genericmessage';
    protected $description = 'Handle generic message';
    protected $version = '1.1.0';
    protected $need_mysql = false;

    public function executeNoDb()
    {
        return Request::emptyResponse();
    }

    public function execute()
    {
        if ($this->getCallbackQuery()) {
            \Longman\TelegramBot\TelegramLog::debug(sprintf('Callback processing'));
            $data = $this->getCallbackQuery()->getData();

            // Handle callback queries
            $params = explode('_', $data);
            if ((isset($params[0]) && $params[0] == 'stop') && (isset($params[1]) && intval($params[1]) != 0)) {
                $this->telegram->app['app.telegram.transports']->
                setMessage($this->getMessage())->
                text(intval($params[1]));
            }
        } else {
            $term = trim($this->getMessage()->getText(true));

            $this->telegram->app['app.telegram.places']->
            setMessage($this->getMessage())->
            text($term);
        }
    }

}
