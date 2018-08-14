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
        $text = htmlspecialchars(addslashes(trim(mb_strtolower($text))));
        return str_replace($this->search, '', $text);
    }
}
