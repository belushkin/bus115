<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\File;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Entities\UserProfilePhotos;
use Longman\TelegramBot\Request;

class WhoamiCommand extends UserCommand
{
    protected $name = 'whoami';
    protected $description = 'Show your id, name and username';
    protected $usage = '/whoami';
    protected $version = '1.1.0';
    protected $private_only = true;

    public function execute()
    {
        $message = $this->getMessage();

        $from       = $message->getFrom();
        $user_id    = $from->getId();
        $chat_id    = $message->getChat()->getId();
        $message_id = $message->getMessageId();

        $data = [
            'chat_id'             => $chat_id,
            'reply_to_message_id' => $message_id,
        ];

        //Send chat action
        Request::sendChatAction([
            'chat_id' => $chat_id,
            'action'  => 'typing',
        ]);

        $caption = sprintf(
            'Your Id: %d' . PHP_EOL .
            'Name: %s %s' . PHP_EOL .
            'Username: %s',
            $user_id,
            $from->getFirstName(),
            $from->getLastName(),
            $from->getUsername()
        );

        //Fetch user profile photo
        $limit    = 10;
        $offset   = null;
        $response = Request::getUserProfilePhotos(
            [
                'user_id' => $user_id,
                'limit'   => $limit,
                'offset'  => $offset,
            ]
        );

        if ($response->isOk()) {
            /** @var UserProfilePhotos $user_profile_photos */
            $user_profile_photos = $response->getResult();

            if ($user_profile_photos->getTotalCount() > 0) {
                $photos = $user_profile_photos->getPhotos();

                /** @var PhotoSize $photo */
                $photo   = $photos[0][2];
                $file_id = $photo->getFileId();

                $data['photo']   = $file_id;
                $data['caption'] = $caption;

                $result = Request::sendPhoto($data);

                //Download the photo after send message response to speedup response
                $response2 = Request::getFile(['file_id' => $file_id]);
                if ($response2->isOk()) {
                    /** @var File $photo_file */
                    $photo_file = $response2->getResult();
                    Request::downloadFile($photo_file);
                }

                return $result;
            }
        }

        //No Photo just send text
        $data['text'] = $caption;

        return Request::sendMessage($data);
    }
}
