<?php

namespace Bus115\Messenger\Messages;

use Silex\Application;
use Bus115\Entity\Geocode;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class Location implements MessageInterface
{

    private $app;
    private $term;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function text($term = '', $telegram = false)
    {
        // Save term for the internal usage
        $this->term = $term;

        // First search in the database for already saved locations
        $geocode = new Geocode();
        $geo = $this->app['em']->getRepository('Bus115\Entity\Geocode')->findOneBy(
            array('changed' => $geocode->prepare($term))
        );
        if (!empty($geo)) {
            $this->app['monolog']->info("ENGINE LOCAL" . $term);
            if ($telegram) {
                return [$geo->getLat(), $geo->getLng()];
            }
            return $this->response($geo->getLat(), $geo->getLng());
        }

        // Second search in Google
        try {
            $results = $this->app['app.api']->getGoogleCoordinates($term);
        } catch (\InvalidArgumentException $e) {
            $results = [];
        }
        if (!empty($results)) {
            $this->app['monolog']->info("ENGINE GOOGLE" . $term);
            return $this->getStopsByGoogleCoordinates($results, $telegram);
        }

        // Third search by Nominatim (Open Streets Map)
        try {
            $results = $this->app['app.api']->getNominatimCoordinates($term);
        } catch (\InvalidArgumentException $e) {
            $results = [];
        }
        if (!empty($results)) {
            $this->app['monolog']->info("ENGINE NOMINATIM" . $term);
            return $this->getStopsByNominatimCoordinates($results, $telegram);
        }

        // If nothing found then switch off
        return $this->app['app.fallback']->text('');
    }

    // Google
    private function getStopsByGoogleCoordinates($results, $telegram)
    {
        if (empty($results->results[0]->geometry->location)) {
            return $this->app['app.fallback']->text('');
        }

        $location = $results->results[0]->geometry->location;

        // Save Geo into the database
        $geo = new Geocode();
        $geo->setOriginal($this->term);
        $geo->setLat($location->lat);
        $geo->setLng($location->lng);
        try {
            $this->app['em']->persist($geo);
            $this->app['em']->flush();
        } catch (UniqueConstraintViolationException $e) {

        }
        if ($telegram) {
            return [$location->lat, $location->lng];
        }
        return $this->response($location->lat, $location->lng);
    }

    // Nominatim
    private function getStopsByNominatimCoordinates($results, $telegram)
    {
        if (empty($results[0]->place_id)) {
            return $this->app['app.fallback']->text('');
        }

        $location = $results[0];

        // Save Geo into the database
        $geo = new Geocode();
        $geo->setOriginal($this->term);
        $geo->setLat($location->lat);
        $geo->setLng($location->lng);
        try {
            $this->app['em']->persist($geo);
            $this->app['em']->flush();
        } catch (UniqueConstraintViolationException $e) {

        }
        if ($telegram) {
            return [$location->lat, $location->lon];
        }
        return $this->response($location->lat, $location->lon);
    }

    private function response($lat, $lng)
    {
        $attachment = [
            'payload' => [
                'coordinates' => [
                    'lat' => $lat,
                    'long' => $lng
                ]
            ]
        ];
        return $this->app['app.stops']->text($attachment);
    }

}
