<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class FirstHandShake implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($payload = '')
    {
        $responses[] = [
            'text' => "Напишіть, звідки Ви вирушаєте - назву вулиці, провулку, площі тощо або назву зупинки. Або ж надішліть локацію",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

}
