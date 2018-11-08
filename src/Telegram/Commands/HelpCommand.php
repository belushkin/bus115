<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class HelpCommand extends UserCommand
{
    protected $name = 'help';
    protected $description = 'A command for help';
    protected $usage = '/help';
    protected $version = '1.0.0';

    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();
        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Напиши назву вулиці де знаходиться зупинка, або напиши "де я".',
        ];

        return Request::sendMessage($data);
    }
}
