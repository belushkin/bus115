<?php

namespace Bus115\Messenger;

class TrimHelper
{
    private $search = [
        'вулиця',
        'провулок',
        'площа',
        'вул.',
        'улица'
    ];

    public function trim($text)
    {
        return str_replace($this->search, '', $text);
    }
}
