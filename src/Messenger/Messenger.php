<?php

// src/Messenger/Messenger.php
namespace Bus115\Messenger;

class Messenger
{

    // Handles messages events
    public function handleMessage($senderPsid, $receivedMessage)
    {

    }

    // Handles messaging_postbacks events
    public function handlePostback($senderPsid, $receivedPostback)
    {

    }

    // Sends response messages via the Send API
    public function callSendAPI($senderPsid, $response)
    {

    }

}
