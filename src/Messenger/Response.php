<?php

namespace Bus115\Messenger;

class Response
{

    public function generateGenericResponse($elements = [])
    {
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
