<?php

namespace Bus115\Timetable;

use Silex\Application;
use GuzzleHttp\Client;

class Requester
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getStopsNearPoint($lat, $lng)
    {
        $client = new Client();
        $response = $client->request('GET', $this->app['eway']['url'], [
            'query' => [
                'login'     => $this->app['eway']['login'],
                'password'  => $this->app['eway']['pass'],
                'function'  => 'stops.GetStopsNearPointWithRoutes',
                'city'      => $this->app['eway']['city'],
                'lat'       => $lat,
                'lng'       => $lng
            ]
        ]);

        return \GuzzleHttp\json_decode($response->getBody());
    }

}
