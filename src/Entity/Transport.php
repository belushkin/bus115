<?php

namespace Bus115\Entity;

/**
 * @ORM\Table(name="stops")
 * @ORM\Entity(repositoryClass="Bus115\Repository\TransportRepository")
 */
class Transport
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $eway_id;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    private $direction;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    private $description;

    /**
     * @ORM\Column(type="string", columnDefinition="ENUM('tram', 'bus', 'metro', 'marshrutka')")
     */
    private $type;

    public function __construct()
    {
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDirection()
    {
        return $this->direction;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getEwayId()
    {
        return $this->eway_id;
    }

}
