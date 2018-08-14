<?php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="images")
 * @ORM\Entity(repositoryClass="Bus115\Repository\ImageRepository")
 */
class Image
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=false)
     */
    private $description;

    public function __construct()
    {
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        return $this->description = $description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}
