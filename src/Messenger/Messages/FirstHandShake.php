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
            'text' => "Вітаю! Скажіть де Ви?",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

}
