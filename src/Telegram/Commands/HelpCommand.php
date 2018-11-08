<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Update;

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
        $user_id = $message->getFrom()->getId();
        $text    = trim($message->getText(true));

//        $keyboard = new Keyboard([
//            ['text' => 'test', '' /* I DONT KNOW HOW TO TELL TO EXECUTE /test COMMAND HERE */]
//        ]);

        if ($text === '') {
            $data['text']           = 'Choose something';
            $data['chat_id']        = $chat_id;
            $data['reply_markup']   = new Keyboard(['Need some help', 'Who am I?']);
            return Request::sendMessage($data);
        }

        $update = json_decode($this->update->toJson(), true);
        if ($text === 'Need some help') {
            $update['message']['text'] = '/test';
            return (new TestCommand($this->telegram, new Update($update)))->preExecute();
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => 'Напиши назву вулиці де знаходиться зупинка, або напиши "де я".',
        ];

        return Request::emptyResponse();
        //return Request::sendMessage($data);
    }
}
