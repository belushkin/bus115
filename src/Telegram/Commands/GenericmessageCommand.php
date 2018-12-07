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
        $term = trim($this->getMessage()->getText(true));

        return Request::emptyResponse();
        $this->telegram->app['app.telegram.places']->
        setMessage($this->getMessage())->
        setTelegram($this->getTelegram())->
        text($term);
    }

}
