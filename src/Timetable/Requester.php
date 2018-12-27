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

    public function request($url)
    {
        $client = new Client();
        $response = $client->request('GET', $url);
        return (string)$response->getBody();
    }

}
