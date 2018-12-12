<?php

namespace Bus115\Messenger;

class TrimHelper
{
    private $search = [
        'ул', 'ул.', 'вул', 'вул.', 'виця',
        'улица', 'улиця', 'уліца', 'остановка', 'житловий комлекс',
        'житловый комплекс', 'станція метро', 'станция метро'
    ];

    public function trim($text)
    {
        $text = htmlspecialchars(addslashes(trim(mb_strtolower($text))));
        return trim(str_replace($this->search, '', $text));
    }
}


(   0 =>    array(
        'entities' =>
            array (
                'location' =>
                    array (
                        0 =>
                            array (
                                'suggested' => true,
                                'confidence' => 0.93736,
                                'value' => 'шамо',
                                'type' => 'value',
                            ),
                    ),
            ),
    'confidence' => 0.95240051390731,
    'value' => 'шамо',
    'type' => 'value',
),
)