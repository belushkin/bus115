<?php

namespace Bus115\Timetable;

use Silex\Application;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Bus115\Entity\Timetable as T;

class Timetable
{

    private $app;

    private $url = 'https://dimas.life/kpt/lite.php?a_day=%s&a_type=%s&a_route=%04d';
    private $type;
    private $id;

    const WORKING_DAY   = 'rob';
    const HOLIDAY_DAY   = 'vih';

    const BUS           = 'avt';
    const TROLLEYBUS    = 'trol';
    const TRAM          = 'tram';

    private $bus = [
        1,2,5,6,7,9,11,12,14,17,18,19,20,21,23,24,27,28,30,31,
        32,33,35,37,39,41,42,43,45,46,47,48,49,51,52,53,54,55,56,
        59,61,62,63,64,69,70,72,73,75,77,78,79,81,87,88,90,91,95,97,98,
        99,100,101,102,104,108,114,115,117,118,119,258
    ];

    private $tram = [
        1,4,5,8,11,12,14,15,16,17,18,19,22,23,25,28,29,33,35
    ];

    private $trol = [
        1,3,5,6,7,8,9,11,12,14,15,16,17,18,19,22,23,24,25,26,27,28,
        29,30,31,32,33,34,35,37,38,39,40,41,42,43,44,45,47,50
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getUrl($id)
    {
        try {
            return sprintf($this->url, $this->getDay(), $this->getTypeById($id), $id);
        } catch (InvalidArgumentException $e) {
            return null;
        }
    }

    private function addLeadingZero($id)
    {
        //return str_pad($id, 4, '0', STR_PAD_RIGHT);
        //return sprintf('%04d', $id);
    }

    public function setTypeWithId($type, $id)
    {
        if (in_array($type, [self::BUS, self::TROLLEYBUS, self::TRAM])) {
            if ($this->validateTypeWithId($type, $id)) {
                $this->type = $type;
                $this->id   = $id;
                return true;
            }
        }
        return false;
    }

    private function validateTypeWithId($type, $id)
    {
        if (in_array($id, $this->bus) && $type == self::BUS) {
            return self::BUS;
        }
        if (in_array($id, $this->tram) && $type == self::TRAM) {
            return self::TRAM;
        }
        if (in_array($id, $this->trol) && $type == self::TROLLEYBUS) {
            return self::TROLLEYBUS;
        }
        return false;
    }

    private function getTypeById($id)
    {
        if (in_array($id, $this->bus)) {
            return self::BUS;
        }
        if (in_array($id, $this->tram)) {
            return self::TRAM;
        }
        if (in_array($id, $this->trol)) {
            return self::TROLLEYBUS;
        }
        throw new InvalidArgumentException();
    }

    private function getDay()
    {
        if (in_array(date('w'), [0,6])) {
            return self::HOLIDAY_DAY;
        }
        return self::WORKING_DAY;
    }

    public function saveEntity($transportNumber, $transportType, $header, $body)
    {
        $t = new T();
        $t->setTransportNumber($transportNumber);
        $t->setTransportType($transportType);
        $t->setHeader($header);
        $t->setTimetable($body);

        $this->app['em']->persist($t);
        $this->app['em']->flush();
    }

}
