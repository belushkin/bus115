<?php

namespace Bus115\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="geocodes")
 * @ORM\Entity(repositoryClass="Bus115\Repository\GeocodeRepository")
 */
class Geocode
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
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $original;

    /**
     * @ORM\Column(type="string", length=255, unique=true, nullable=false)
     */
    private $key;

    /**
     * @ORM\Column(type="float", unique=false, nullable=false)
     */
    private $lat;

    /**
     * @ORM\Column(type="float", unique=false, nullable=false)
     */
    private $lng;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    private $vowels = array("a", "e", "i", "o", "u",
        "A", "E", "I", "O", "U", " ", ".", ",", "-", ")", "("
    );

    public function __construct()
    {
        $this->date_created = new \DateTime();
        $this->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function setOriginal($original)
    {
        $this->original = $this->doTranslit($original);
        $this->setKey($original);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function setKey($original)
    {
        $this->key = $this->prepare($original);
    }

    public function prepare($original)
    {
        $translitted = $this->doTranslit($original);
        return str_replace($this->vowels, "", $translitted);
    }

    public function getLat()
    {
        return $this->lat;
    }

    public function setLat($lat)
    {
        $this->lat = $lat;
    }

    public function getLng()
    {
        return $this->lng;
    }

    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    private function doTranslit($st)
    {
        $replacement = array(
            "й"=>"i","ц"=>"c","у"=>"u","к"=>"k","е"=>"e","н"=>"n",
            "г"=>"g","ш"=>"sh","щ"=>"sh","з"=>"z","х"=>"x","ъ"=>"\'",
            "ф"=>"f","ы"=>"i","в"=>"v","а"=>"a","п"=>"p","р"=>"r",
            "о"=>"o","л"=>"l","д"=>"d","ж"=>"zh","э"=>"ie","ё"=>"e",
            "я"=>"ya","ч"=>"ch","с"=>"c","м"=>"m","и"=>"i","т"=>"t",
            "ь"=>"\'","б"=>"b","ю"=>"yu",
            "Й"=>"I","Ц"=>"C","У"=>"U","К"=>"K","Е"=>"E","Н"=>"N",
            "Г"=>"G","Ш"=>"SH","Щ"=>"SH","З"=>"Z","Х"=>"X","Ъ"=>"\'",
            "Ф"=>"F","Ы"=>"I","В"=>"V","А"=>"A","П"=>"P","Р"=>"R",
            "О"=>"O","Л"=>"L","Д"=>"D","Ж"=>"ZH","Э"=>"IE","Ё"=>"E",
            "Я"=>"YA","Ч"=>"CH","С"=>"C","М"=>"M","И"=>"I","Т"=>"T",
            "Ь"=>"\'","Б"=>"B","Ю"=>"YU",
        );

        foreach($replacement as $i=>$u) {
            $st = mb_eregi_replace($i,$u,$st);
        }
        return $st;
    }

}
