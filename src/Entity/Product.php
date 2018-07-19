<?php
// src/Entities/Product.php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity @ORM\Table(name="products")
 **/
class Product
{

    /** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue **/
    private $id;

    /** @ORM\Column(type="string") **/
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getId()
    {
        return $this->id;
    }
}
