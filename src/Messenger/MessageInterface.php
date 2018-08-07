<?php

namespace Bus115\Messenger;

interface MessageInterface
{

    public function handle($senderPsid, $receivedMessage);
}
