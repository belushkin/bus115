<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

use Silex\Application;

class Messenger
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    // Handles messages events
    public function handleMessage($senderPsid, $receivedMessage)
    {
        $responses = [];

        if (isset($receivedMessage['text'])) {
            $responses = $this->handleSimpleTextMessage($receivedMessage['text']);
        } else if (isset($receivedMessage['attachments'])) {
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $responses = $this->handleLocationMessage($attachment);
                }
            }
        }

        foreach ($responses as $response) {
            $this->app['monolog']->info(sprintf('Handle Message'));
            $this->callSendAPI($senderPsid, $response);
        }
    }

    // Handles messaging_postbacks events
    public function handlePostback($senderPsid, $receivedPostback)
    {
        $responses = [];

        // Get the payload for the postback
        $payload = $receivedPostback['payload'];
        $this->app['monolog']->info(sprintf('Payload: %s', $payload));

        // Set the response based on the postback payload
        if (intval($payload) != 0 && strpos($payload, '_') === false) { // just show stop info
            $responses = $this->handleStopInfo($payload);
        } else if (strpos($payload, '_')) { // show specific arrival time for the transport
            $responses = $this->handleTransportInfo($payload);
        } else if ($payload === 'first hand shake') {
            $responses[] = [
                'text' => "Вітаю! Скажіть де Ви?",
                'quick_replies' => [
                    [
                        'content_type' => 'location',

                    ]
                ]
            ];
        }

        foreach ($responses as $response) {
            $this->app['monolog']->info(sprintf('Handle Postback'));
            $this->callSendAPI($senderPsid, $response);
        }
    }

    // Sends response messages via the Send API
    public function callSendAPI($senderPsid, $response)
    {
        $url = 'https://graph.facebook.com/v2.6/me/messages?access_token=' . $this->app['eway']['page_access_token'];

        $requestBody = [
            'recipient' => [
                'id' => $senderPsid
            ],
            'message' => $response
        ];

        $ch = curl_init($url);

        if ($requestBody['message']['text']) {
            $this->app['monolog']->info(sprintf('Sent message back: %s', $response['text']));
        } else {
            $this->app['monolog']->info(sprintf('Sent attachment back, %s', \GuzzleHttp\json_encode($requestBody)));
        }
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, \GuzzleHttp\json_encode($requestBody));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_exec($ch);
    }

    private function handleLocationMessage(Array $attachment = [])
    {
        $lat        = $attachment['payload']['coordinates']['lat'];
        $lng        = $attachment['payload']['coordinates']['long'];

        $body       = $this->app['app.eway']->getStopsNearPoint($lat, $lng);
        if (isset($body->stop) && is_array($body->stop) && !empty($body->stop)) {
            $elements   = [];
            $responses  = [];
            $i = 0;
            foreach ($body->stop as $stop) {
                $elements[] = [
                    'title' => $stop->title,
                    'image_url' => 'https://bus115.kiev.ua/images/stop.jpg',
                    'buttons' => [
                        [
                            'type' => 'postback',
                            'title' => 'Вибрати цю зупинку',
                            'payload' => $stop->id
                        ]
                    ]
                ];
                $i++;
                if ($i % 10 == 0) {
                    $responses[] = $this->generateGenericResponse($elements);
                    $elements = [];
                }
            }
            $responses[] = $this->generateGenericResponse($elements);
            return $responses;
        }

        $responses[] = [
            'text' => "Нажаль, нічого не знайдено.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

    private function handleStopInfo($id = 0)
    {
        $routes     = $this->callStopInfo($id);
        $elements   = [];
        $responses  = [];

        $i = 0;
        foreach ($routes as $route) {
            $elements[] = [
                'title'     => $route->transportName . ' №' . $route->title,
                'subtitle'  => 'В напрямку:' . $route->directionTitle,
                'image_url' => "https://bus115.kiev.ua/images/{$route->transportKey}.jpg",
                'buttons' => [
                    [
                        'type' => 'postback',
                        'title' => 'Оновити час прибуття',
                        'payload' => $id . '_'. $route->id
                    ]
                ]
            ];
            $i++;
            if ($i % 10 == 0) {
                $responses[] = $this->generateGenericResponse($elements);
                $elements = [];
            }
        }
        $responses[] = $this->generateGenericResponse($elements);
        return $responses;
    }

    private function generateGenericResponse($elements = [])
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

    private function callStopInfo($id)
    {
        $body = $this->app['app.eway']->handleStopInfo($id);
        if (isset($body->routes) && is_array($body->routes) && !empty($body->routes)) {
            return $body->routes;
        }
        return [];
    }

    private function handleTransportInfo($payload = '')
    {
        $params = explode('_', $payload);
        if (intval($params[0]) == 0) { // stop id
            return [];
        }

        $body       = $this->app['app.eway']->handleStopInfo($params[0]);
        if (isset($body->routes) && is_array($body->routes)) {
            foreach ($body->routes as $route) {
                if ($route->id == $params[1]) {
                    $string = $route->transportName . ' №' . $route->title . ', ';
                    $string .= 'в напрямку: ' . $route->directionTitle . ', ';
                    $string .= "буде через " . $route->timeLeft . ' хвилин';
                    $responses[] = [
                        'text' => $string
                    ];
                    return $responses;
                }
            }
        }
        $responses[] = [
            'text' => "Час прибуття невідомий, оновіть своє місцезнаходження.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        return $responses;
    }

    private function handleSimpleTextMessage($term = '')
    {
        if (!empty($term)) {
            $body       = $this->app['app.eway']->getPlacesByName($term);
            if (isset($body->item) && is_array($body->item) && !empty($body->item)) {
                $i = 0;
                $elements   = [];
                $responses  = [];
                foreach ($body->item as $item) {
                    $elements[] = [
                        'title'     => $item->title,
                        'subtitle'  => 'В напрямку:',
                        'image_url' => "https://bus115.kiev.ua/images/stop.jpg",
                        'buttons' => [
                            [
                                'type' => 'postback',
                                'title' => 'Вибрати цю зупинку',
                                'payload' => $item->id
                            ]
                        ]
                    ];
                    $i++;
                    if ($i % 10 == 0) {
                        $responses[] = $this->generateGenericResponse($elements);
                        $elements = [];
                    }
                }
                $responses[] = $this->generateGenericResponse($elements);
                return $responses;
            }
        }

        $responses[] = [
            'text' => "Нажаль, по запросу: ".htmlspecialchars(stripslashes($term))." нічого не знайдено.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];

        return $responses;
    }

}
