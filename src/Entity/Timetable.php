<?php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="timetable")
 * @ORM\Entity(repositoryClass="Bus115\Repository\TimetableRepository")
 */
class Timetable
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
     * @ORM\Column(type="string", length=4, unique=false, nullable=true)
     */
    private $transport_number;

    /**
     * @ORM\Column(type="string", length=4, unique=false, nullable=true)
     */
    private $transport_type;

    /**
     * @ORM\Column(type="json", length=2500, unique=false, nullable=true)
     */
    private $header;

    /**
     * @ORM\Column(type="json", length=2500, unique=false, nullable=true)
     */
    private $timetable;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
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

    public function setTransportNumber($transportNumber)
    {
        $this->transport_number = $transportNumber;
    }

    public function getTransportNumber()
    {
        return $this->transport_number;
    }

    public function setTransportType($transportType)
    {
        $this->transport_type = $transportType;
    }

    public function getTransportType()
    {
        return $this->transport_type;
    }

    public function setHeader($header)
    {
        $this->header = $header;
    }

    public function getHeader()
    {
        return $this->header;
    }

    public function setTimetable($timetable)
    {
        $this->timetable = $timetable;
    }

    public function getTimetable()
    {
        return $this->timetable;
    }

}
