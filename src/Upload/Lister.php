<?php

namespace Bus115\Upload;

use Silex\Application;
use Symfony\Component\Validator\Constraints as Assert;

class Lister
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function manage($path, $type, $recentlyAdded = false, $count = false)
    {
        $result = [];
        $i = 0;
        $files = array_diff(scandir($path), array('..', '.'));
        foreach ($files as $file) {
            $info = pathinfo($file);
            $uuid = $info['filename'];
            $errors = $this->app['validator']->validate($uuid, new Assert\Uuid());
            if (count($errors) > 0) {
                continue;
            }
            if ($recentlyAdded) {
                if ($type == Manager::TYPE_TRANSPORT) {
                    $imageData = $this->app['em']->getRepository('Bus115\Entity\Transport')->findOneBy(
                        array('uuid' => $uuid)
                    );
                } else {
                    $imageData = $this->app['em']->getRepository('Bus115\Entity\Stop')->findOneBy(
                        array('uuid' => $uuid)
                    );
                }
            } else {
                $imageData = $this->app['em']->getRepository('Bus115\Entity\Image')->findOneBy(
                    array('uuid' => $uuid)
                );
            }
            if (empty($imageData)) {
                continue;
            }

            $result[] = [
                'name' => $file,
                'uuid' => $uuid,
                'type' => $type,
                'description'   => $imageData->getDescription(),
                'date'          => $imageData->getDateCreated()->format('Y-m-d H:i')
            ];
            $i++;
            if ($count && $i == $count) {
                break;
            }
        }
        return $result;
    }
}
