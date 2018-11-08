<?php

namespace Bus115\Telegram;

use Silex\Application;

class Response
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function generateGenericResponse($elements = [], $chat_id)
    {
        $data = [
            'chat_id'    => $chat_id,
            'parse_mode' => 'markdown',
        ];

        $response = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements' => $elements
                ]
            ]
        ];
        return $response;
    }

}
