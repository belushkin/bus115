<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\KeyboardButton;
use Longman\TelegramBot\Request;

class LocationCommand extends UserCommand
{
    protected $name = 'location';
    protected $description = 'Share your location, works only on mobile devices';
    protected $usage = '/location';
    protected $version = '1.0.0';

    public function execute()
    {
        $button = new KeyboardButton('Location');
        $button->setRequestLocation(true);

        $keyboard = new Keyboard($button);
        $keyboard->setResizeKeyboard(true);

        $data = [
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => 'Надішліть своє місцезнаходження',
            'reply_markup' => $keyboard,
        ];

        return Request::sendMessage($data);
    }
}
