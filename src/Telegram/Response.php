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
        try {
            foreach ($elements as $element) {
                $result = Request::sendVenue($element);
                if ($result->getErrorCode() == 1) {
                    $this->app['monolog']->info("Venues ERROR " . $result->getDescription());
                    return null;
                }
            }
        } catch (\RuntimeException $e) {
            sleep(2);
            $this->app['monolog']->info("Venues runtime exception sleep 2");
        } catch (\Exception $e) {
            sleep(2);
            $this->app['monolog']->info("Venues exception sleep 2");
        }
        return $result;
    }

    public function photos($elements = [])
    {
        $result = null;
        foreach ($elements as $element) {
            $result = Request::sendPhoto($element);
            if ($result->getErrorCode() == 1) {
                $this->app['monolog']->info("Photos ERROR " . $result->getDescription());
                return null;
            }
        }
        return $result;
    }

}
