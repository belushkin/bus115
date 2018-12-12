<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class Location implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        try {
            $results = $this->app['app.api']->getGoogleCoordinates($term);
        } catch (\InvalidArgumentException $e) {
            $results = [];
        }
        return $this->getStopsByGoogleCoordinates($results);
    }

    private function getStopsByGoogleCoordinates($results)
    {
        if (empty($results->results[0]->geometry->location)) {
            return $this->app['app.fallback']->text('');
        }

        $location = $results->results[0]->geometry->location;
        $attachment = [
            'payload' => [
                'coordinates' => [
                    'lat' => $location->lat,
                    'long' => $location->lng
                ]
            ]
        ];
        return $this->app['app.stops']->text($attachment);
    }

}
