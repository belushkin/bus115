<?php

namespace Bus115\Messenger\Messages;

interface MessageInterface
{

    public function text($payload = '');
}
