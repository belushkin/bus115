<?php

namespace Bus115\Telegram;

use Silex\Application;
use Longman\TelegramBot\Request;

class Response
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function venues($elements = [])
    {
        $result = null;
        foreach ($elements as $element) {
            $result = Request::sendVenue($element);
        }
        return $result;
    }

    public function photos($elements = [])
    {
        $result = null;
        foreach ($elements as $element) {
            $result = Request::sendPhoto($element);
        }
        return $result;
    }

}
