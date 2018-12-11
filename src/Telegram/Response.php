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
                if (!$result->isOk()) {
                    $this->app['monolog']->info("Venues ERROR " . $result->getDescription());
                    sleep(2);
                    return Request::emptyResponse();
                }
            }
            sleep(2);
        } catch (\RuntimeException $e) {
            $this->app['monolog']->info("Venues runtime exception sleep 2");
            return Request::emptyResponse();
        } catch (\Exception $e) {
            $this->app['monolog']->info("Venues exception sleep 2");
            return Request::emptyResponse();
        } catch (\Throwable $e) {
            $this->app['monolog']->info("Venues Throwable sleep 2");
            return Request::emptyResponse();
        }
        return $result;
    }

    public function photos($elements = [])
    {
        $result = null;
        try {
            foreach ($elements as $element) {
                $result = Request::sendPhoto($element);
                if (!$result->isOk()) {
                    $this->app['monolog']->info("Photos ERROR " . $result->getDescription());
                    sleep(2);
                    return Request::emptyResponse();
                }
            }
            sleep(2);
        } catch (\RuntimeException $e) {
            $this->app['monolog']->info("Photos runtime exception sleep 2");
            return Request::emptyResponse();
        } catch (\Exception $e) {
            $this->app['monolog']->info("Photos exception sleep 2");
            return Request::emptyResponse();
        } catch (\Throwable $e) {
            $this->app['monolog']->info("Photos Throwable sleep 2");
            return Request::emptyResponse();
        }
        return $result;
    }

}
