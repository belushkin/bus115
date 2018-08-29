<?php

namespace Bus115\Messenger;

use Silex\Application;

class Google implements MessageInterface
{

    private $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function handle($senderPsid, $receivedPostback, $nlp)
    {

    }

}
