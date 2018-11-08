<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Request;

class ExecCommand extends UserCommand
{
    protected $name = 'exec';
    protected $description = 'Exec command';
    protected $usage = '/exec';
    protected $version = '1.0.0';

    public function execute()
    {
        $button = new KeyboardButton('location');
        $button->setRequestLocation(true);

        $keyboard = new Keyboard($button);
        $keyboard->setResizeKeyboard(true);

        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Choose something',
            'reply_markup' => $keyboard,
        ];

        return Request::sendMessage($data);
    }
}
