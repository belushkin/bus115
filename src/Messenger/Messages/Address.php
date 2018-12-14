<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;

class Address implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '')
    {
        // Break point
        if (empty($term) || strlen($term) < 4) {
            return $this->app['app.fallback']->text('');
        }

        // Wit.ai detected that this is address and we try to find it through the eway
        // urlencode term before sending it to eway
        $body       = $this->app['app.eway']->getPlacesByName($term);
        if (empty($body->item)) {
            return false;
        }

        $i = 0;
        $elements   = [];
        $responses  = [];
        $subtitles  = [];
        foreach ($body->item as $item) {
            $subtitle = $this->app['app.stops']->getStopSubtitle($item->routes);
            if (in_array($subtitle, $subtitles)) {
                continue;
            }
            $elements[] = [
                'title'     => $item->title,
                'subtitle'  => 'Транспорт: ' . $subtitle,
                'image_url' => $this->getStopImage($item->id, $item->lat, $item->lng),
                'buttons'   => $this->app['app.stops']->getMessengerStopButtonsArray($item->id, $item->routes)
            ];
            $subtitles[] = $subtitle;
            $i++;
            if ($i % 10 == 0) {
                $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
                $elements = [];
            }
        }
        $responses[] = $this->app['app.messenger_response']->generateGenericResponse($elements);
        return $responses;
    }

    public function getStopImage($id, $lat, $lng)
    {
        $imageUrl = "https://maps.googleapis.com/maps/api/staticmap?center={$lat},{$lng}&zoom=16&size=400x400&maptype=terrain&markers=color:blue%7Clabel:S%7C{$lat},{$lng}&key=" . $this->app['eway']['maps_key'];
        $entity = $this->app['em']->getRepository('Bus115\Entity\Stop')->findOneBy(
            array('eway_id' => $id)
        );
        if ($entity) {
            $imageUrl = "https://bus115.kiev.ua/images/stops/{$entity->getName()}";
        }
        return $imageUrl;
    }
}
