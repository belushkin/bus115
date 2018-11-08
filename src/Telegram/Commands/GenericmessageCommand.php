<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Commands\UserCommands\HelpCommand;
use Longman\TelegramBot\Commands\UserCommands\WhoamiCommand;
use Longman\TelegramBot\Entities\Update;
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
        $text = trim($this->getMessage()->getText(true));

        $this->telegram->app['monolog']->info(sprintf('TELEGRAM SEARCH WORKS, User entered Term: %s', $text));
//        $term = $this->stripTerms($term);
//        $this->app['monolog']->info(sprintf('Term after STRIP: %s', $term));

        \Longman\TelegramBot\TelegramLog::debug('PASOS');

        $update = json_decode($this->update->toJson(), true);

        if ($text === 'Need some help') {
            $update['message']['text'] = '/help';
            return (new HelpCommand($this->telegram, new Update($update)))->preExecute();
        }
        if ($text === 'Who am I?') {
            $update['message']['text'] = '/whoami';
            return (new WhoamiCommand($this->telegram, new Update($update)))->preExecute();
        }

        return Request::emptyResponse();
    }


}
