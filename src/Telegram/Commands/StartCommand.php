<?php

namespace Longman\TelegramBot\Commands\SystemCommands;

use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

class StartCommand extends SystemCommand
{
    protected $name = 'start';
    protected $description = 'Start command';
    protected $usage = '/start';
    protected $version = '1.1.0';
    protected $private_only = true;

    public function execute()
    {
        $message = $this->getMessage();

        $chat_id    = $message->getChat()->getId();
        $text       = 'Я чат-бот розкладу громадського транспорту Києва!'.PHP_EOL;
        $text       .= 'Напишіть, звідки Ви вирушаєте - назву вулиці, провулку, площі тощо або назву зупинки. Або ж надішліть ваше місцезнаходження' .PHP_EOL;
        $text       .= 'Для того, щоб надіслати місцезнаходження, напишіть /location' .PHP_EOL;
        $text       .= 'Для того, щоб побачити перелік доступних команд, напишіть /help';

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
