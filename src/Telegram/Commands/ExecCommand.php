<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class ExecCommand extends UserCommand
{
    protected $name = 'exec';
    protected $description = 'Exec command';
    protected $usage = '/exec';
    protected $version = '1.0.0';

    public function execute()
    {
        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Choose something',
            'reply_markup' => new Keyboard(['Need some help', 'Who am I?']),
        ];

        return Request::sendMessage($data);
    }
}
