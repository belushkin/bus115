<?php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @ORM\Table(name="schedule")
 * @ORM\Entity(repositoryClass="Bus115\Repository\ScheduleRepository")
 */
class Schedule
{

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $uuid;

    /**
     * @ManyToOne(targetEntity="Timetable")
     */
    private $timetable;

    /**
     * @ORM\Column(type="time", unique=false, nullable=true)
     */
    private $start_time;

    /**
     * @ORM\Column(type="integer", length=2, unique=false, nullable=true)
     */
    private $direction;

    /**
     * @ORM\Column(type="string", length=4, unique=false, nullable=true)
     */
    private $period_of_week;

    /**
     * @ORM\Column(type="string", length=200, unique=false, nullable=true)
     */
    private $header;

    /**
     * @ORM\Column(type="string", length=200, unique=false, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    public function getDateCreated()
    {
        return $this->date_created;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStartTime($startTime)
    {
        $this->start_time = $startTime;
    }

    public function getStartTime()
    {
        return $this->start_time;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function setDirection($direction)
    {
        $this->direction = $direction;
    }

    public function getTimetable()
    {
        return $this->timetable;
    }

    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setPeriodOfWeek($periodOfWeek)
    {
        $this->period_of_week = $periodOfWeek;
    }

    public function getPeriodOfWeek()
    {
        return $this->period_of_week;
    }
}
