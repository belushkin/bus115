<?php

namespace Bus115\Timetable;

use Silex\Application;
use GuzzleHttp\Client;

class DomManager
{

    private $app;
    private $domDocument;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->domDocument = new \DOMDocument('1.0', 'UTF-8');
    }

    public function loadHTML($html)
    {
        // set error level
        $internalErrors = libxml_use_internal_errors(true);
        $this->domDocument->loadHTML($html);
        // Restore error level
        libxml_use_internal_errors($internalErrors);
    }

    private function getElementById($id)
    {
        $xpath = new \DOMXPath($this->domDocument);
        return $xpath->query("//*[@id='$id']")->item(0);
    }

    public function getHeaderInJson()
    {
        $result = [];
        $header = $this->getElementById('timetable_header');
        $tds = $header->getElementsByTagName('td');
        foreach ($tds as $node) {
            $result[] = $node->nodeValue;
        }
        return \GuzzleHttp\json_encode($result);
    }

    public function storeSchedule()
    {
        $result = [];
        $body   = $this->getElementById('timetable');
        $trs    = $body->getElementsByTagName('tr');
        $header = true;
        $size   = 0;

        foreach ($trs as $node) {
            // Define directions in first tr in the table
            if ($header) {
                $size = $this->getSize($node);
                $header = false;
                continue;
            }


            var_dump($node);

            //$result[] = $node->nodeValue;
        }
        exit();
        return \GuzzleHttp\json_encode($result);
    }

    private function getSize($node)
    {
        $tds = $node->getElementsByTagName('td');

        $previousTD = null;
        $i          = 0;
        $size       = 0;
        foreach ($tds as $td) {
            if ($td->nodeValue == $previousTD) {
                $size = $i;
            }
            $previousTD = $td->nodeValue;
            $i++;
        }
        return $size;
    }

}
