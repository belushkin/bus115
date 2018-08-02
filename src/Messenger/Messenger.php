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
        if (isset($receivedMessage['text']) && $receivedMessage['text'] == 'location') {
            $response = [
                'text' => "Скажіть де Ви!",
                'quick_replies' => [
                    [
                        'content_type' => 'location',

                    ]
                ]
            ];
        } else if (isset($receivedMessage['text'])) {
            // Create the payload for a basic text message
            $response = [
              'text' => "You sent the message: '{$receivedMessage['text']}'. Now send me an image!"
            ];
        } else if (isset($receivedMessage['attachments'])) {
            $urls = [];
            foreach ($receivedMessage['attachments'] as $attachment) {
                if ($attachment['type'] == 'location') {
                    $response = $this->handleLocationMessage($attachment);
                } else {
                    $response = $this->handleImageMessage($attachment);
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

        // Set the response based on the postback payload
        if ($payload === 'yes') {
            $response = [
                'text' => "Thanks!"
            ];
        } else if ($payload === 'no') {
            $response = [
                'text' => "Oops, try sending another image."
            ];
        } else if (intval($payload) != 0) {
            $response = $this->handleStopInfo($payload);
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
                            'payload' => $route->id
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

    private function handleImageMessage(Array $attachment = [])
    {
        $response   = [];
        $urls[]     = $attachment['payload']['url'];

        $response = [
            'attachment' => [
                'type' => 'template',
                'payload' => [
                    'template_type' => 'generic',
                    'elements' => [
                        [
                            'title' => 'Is this the right picture?',
                            'subtitle' => 'Tap a button to answer.',
                            'image_url' => current($urls),
                            'buttons' => [
                                [
                                    'type' => 'postback',
                                    'title' => 'Yes!',
                                    'payload' => 'yes'
                                ],
                                [
                                    'type' => 'postback',
                                    'title' => 'No!',
                                    'payload' => 'no'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $response;
    }

}
