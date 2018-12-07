<?php

namespace Bus115\Messenger;

class TrimHelper
{
    private $search = [
        'ул', 'ул.', 'вул', 'вул.',
        'улица', 'улиця', 'уліца', 'остановка', 'житловий комлекс',
        'житловый комплекс', 'станція метро', 'станция метро'
    ];

    public function trim($text)
    {
        $text = htmlspecialchars(addslashes(trim(mb_strtolower($text))));
        return trim(str_replace($this->search, '', $text));
    }
}
