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

        $file->move($path, $image->getUuid() . '.' . $file->getClientOriginalExtension());
        return true;
    }

    public function move($type, $uuid, $ewayId, $name)
    {
        $image = $this->app['em']->getRepository('Bus115\Entity\Image')->findOneBy(
            array('uuid' => $uuid)
        );
        if (!$image) {
            return false;
        }
        if ($type == self::TYPE_STOP) {
            $pathFrom = __DIR__.'/../../public/upload/'.self::FOLDER_STOPS.'/'.$name;
            $pathTo = __DIR__.'/../../public/images/'.self::FOLDER_STOPS.'/'.$name;
            $entity = new Stop();
            $entity->setDescription($image->getDescription());
            $entity->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
            $entity->setEwayId($ewayId);
            $this->app['em']->persist($entity);
            $this->app['em']->flush();
            rename ($pathFrom, $pathTo);
            return true;
        } else if($type == self::TYPE_TRANSPORT) {
            $pathFrom = __DIR__.'/../../public/upload/'.self::FOLDER_TRANSPORTS.'/'.$name;
            $pathTo = __DIR__.'/../../public/images/'.self::FOLDER_TRANSPORTS.'/'.$name;
            $entity = new Transport();
            $entity->setDescription($image->getDescription());
            $entity->setUuid(\Ramsey\Uuid\Uuid::uuid4()->toString());
            $entity->setEwayId($ewayId);
            $this->app['em']->persist($entity);
            $this->app['em']->flush();
            rename ($pathFrom, $pathTo);
            return true;
        }
        return false;
    }

}
