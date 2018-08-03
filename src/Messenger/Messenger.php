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
        $response = [];

        // Check if the message contains text
        if (isset($receivedMessage['text']) && strtolower($receivedMessage['text']) == 'location') {
            $response = [
                'text' => "Скажіть, де Ви!",
                'quick_replies' => [
                    [
                        'content_type' => 'location',

                    ]
                ]
            ];
        } else if (isset($receivedMessage['text'])) {
            // Create the payload for a basic text message
            $response = [
              'text' => "You sent the message: '{$receivedMessage['text']}'"
            ];
        } else if (isset($receivedMessage['attachments'])) {
            $urls = [];
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $response = $this->handleLocationMessage($attachment);
                }
            }
        }

        $this->app['monolog']->info(sprintf('Response: %s', var_export($response, true)));
        // Sends the response message
        $this->callSendAPI($senderPsid, $response);
    }

    // Handles messaging_postbacks events
    public function handlePostback($senderPsid, $receivedPostback)
    {
        $response = [];

        // Get the payload for the postback
        $payload = $receivedPostback['payload'];
        $this->app['monolog']->info(sprintf('Payload: %s', $payload));

        // Set the response based on the postback payload
        if (intval($payload) != 0 && !strpos('|', $payload)) { // just show stop info
            $response = $this->handleStopInfo($payload);
        } else if (intval($payload) != 0 && strpos('|', $payload)) { // show specific arrival time for the transport
            $response = $this->handleTransportInfo($payload);
        } else if ($payload === 'first hand shake') {
            $response = [
                'text' => "Вітаю! Скажіть де Ви?",
                'quick_replies' => [
                    [
                        'content_type' => 'location',

                    ]
                ]
            ];
        }
        // Sends the response message
        $this->callSendAPI($senderPsid, $response);
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
        $elements   = [];
        if (isset($body->stop) && is_array($body->stop)) {
            foreach ($body->stop as $stop) {
                $elements[] = [
                    'title' => $stop->title,
                    'image_url' => 'https://bus115.kiev.ua/images/stop.jpg',
                    'buttons' => [
                        [
                            'type' => 'postback',
                            'title' => 'Вибрати зупинку: ' . $stop->title,
                            'payload' => $stop->id
                        ]
                    ]
                ];
            }
        }

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

    private function handleStopInfo($id = 0)
    {
        $body       = $this->app['app.eway']->handleStopInfo($id);
        $elements   = [];
        if (isset($body->routes) && is_array($body->routes)) {
            foreach ($body->routes as $route) {
                $elements[] = [
                    'title'     => $route->transportName . ' №' . $route->title,
                    'subtitle'  => 'В напрямку:' . $route->directionTitle,
                    'image_url' => "https://bus115.kiev.ua/images/{$route->transportKey}.jpg",
                    'buttons' => [
                        [
                            'type' => 'postback',
                            'title' => 'Оновити час прибуття',
                            'payload' => $id . '|'. $route->id
                        ]
                    ]
                ];
            }
        }
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

    private function handleTransportInfo($payload = '')
    {
        $params = explode('|', $payload);
        if (intval($params[0]) == 0) { // stop id
            return [];
        }

        $response = [
            'text' => "Час прибуття невідомий, оновіть своє місцезнаходження.",
            'quick_replies' => [
                [
                    'content_type' => 'location',

                ]
            ]
        ];
        $body       = $this->app['app.eway']->handleStopInfo($params[0]);
        if (isset($body->routes) && is_array($body->routes)) {
            foreach ($body->routes as $route) {
                if ($route->id == $params[1]) {
                    $response = [
                        'text' => "Буде через " . $route->timeLeft . 'хвилин'
                    ];
                }
            }
        }
        return $response;
    }

}
