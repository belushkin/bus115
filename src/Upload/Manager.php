<?php

namespace Bus115\Upload;

use Bus115\Entity\Image;
use Bus115\Entity\Stop;
use Bus115\Entity\Transport;

use Silex\Application;

class Manager
{

    private $app;

    const FOLDER_STOPS = 'stops';
    const FOLDER_TRANSPORTS = 'transports';

    const TYPE_STOP = 'stop';
    const TYPE_TRANSPORT = 'transport';

    const ALLOWED_FILESIZE_MB = 10;

    private $allowedExtensions = [
        'jpeg',
        'jpg',
        'png'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function manage($file, $description, $type = self::TYPE_STOP)
    {
        $path = ($type == self::TYPE_STOP) ? __DIR__.'/../../public/upload/'.self::FOLDER_STOPS.'/' : __DIR__.'/../../public/upload/'.self::FOLDER_TRANSPORTS.'/';

        $image = new Image();
        $image->setDescription($description);
        $image->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
        $this->app['em']->persist($image);
        $this->app['em']->flush();

        if (in_array($file->guessClientExtension(), $this->allowedExtensions) && $this->filesize($file->getSize()) < self::ALLOWED_FILESIZE_MB) {
            $file->move($path, $image->getUuid() . '.' . $file->getClientOriginalExtension());
            return true;
        }
        return false;
    }

    public function move($type, $uuid, $ewayId, $name, $transportType)
    {
        $image = $this->app['em']->getRepository('Bus115\Entity\Image')->findOneBy(
            array('uuid' => $uuid)
        );
        if (!$image) {
            return false;
        }
        if ($type == self::TYPE_STOP) {
            $folder = self::FOLDER_STOPS;
            $entity = new Stop();
        } else {
            $folder = self::FOLDER_TRANSPORTS;
            $entity = new Transport();
            $entity->setType($transportType);
        }
        $pathFrom = ROOT_FOLDER .'/public/upload/'.$folder.'/'.$name;
        $pathTo = ROOT_FOLDER .'/public/images/'.$folder.'/'.$name;
        $entity->setDescription($image->getDescription());
        $entity->setUuid($image->getUuid());
        $entity->setEwayId($ewayId);

        $this->app['em']->persist($entity);
        $this->app['em']->remove($image);
        $this->app['em']->flush();

        rename ($pathFrom, $pathTo);
        return true;
    }

    private function filesize($bytes, $decimals = 2)
    {
        return round ($bytes / 1048576, $decimals);
    }

}
