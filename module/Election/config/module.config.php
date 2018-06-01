<?php

namespace Election;

use Zend\Router\Http\Segment;

return [
    // The following section is new and should be added to your file:
    'router' => [
        'routes' => [
            'election' => [
                'type'    => Segment::class,
                'options' => [
                    'route' => '/election[/:action[/:id]]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => Controller\ElectionController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'template_path_stack' => [
            'election' => __DIR__ . '/../view',
        ],
    ],
];