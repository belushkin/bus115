<?php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="stops", indexes={@ORM\Index(name="eway_id", columns={"eway_id"})})
 * @ORM\Entity(repositoryClass="Bus115\Repository\StopRepository")
 */
class Stop
{
    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $eway_id;

    /**
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $uuid;

    /**
     * @ORM\Column(type="string", length=50, unique=false, nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=50, unique=true, nullable=false)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $direction;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        return $this->description = $description;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEwayId()
    {
        return $this->eway_id;
    }

    public function setEwayId($ewayId)
    {
        $this->eway_id = $ewayId;
    }

    public function setName($name)
    {
        $this->name = $name;
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
}
