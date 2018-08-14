<?php

namespace Bus115\Upload;

use Bus115\Entity\Image;
use Silex\Application;

class Manager
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function manage($file, $description, $type = 'stop')
    {
        $path = ($type == 'stop') ? __DIR__.'/../../public/upload/stops/' : __DIR__.'/../../public/upload/transports/';

        $image = new Image();
        $image->setDescription($description);
        $this->app['em']->persist($image);
        $this->app['em']->flush();

        $file->move($path, $image->getId() . '.' . $file->getClientOriginalExtension());
        return true;
    }
}
