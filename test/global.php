<?php
return [
    'router' => [
        'routes' => [
            'index' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/',
                    'defaults' => [
                    ],
                ],
            ],
            'dashboard' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/dashboard[/]',
                    'defaults' => [
                    ],
                ],
            ],
            'servers' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/servers[/[:action]]',
                    'defaults' => [
                    ],
                ],
            ],
            'server' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/server/:hostname[/[:action]]',
                    'defaults' => [
                    ],
                ],
            ],
            'puppet' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet[/[:controller[/:action]]]',
                    'defaults' => [
                    ],
                ],
            ],
            'puppet-environment' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/environment/:id/:action',
                    'defaults' => [
                    ],
                ],
            ],
            'puppet-environment-user-remove' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '[/env/:envId]/puppet/environment/:id/user/:userId/remove',
                    'defaults' => [
                    ],
                ],
            ],
        ],
    ],
];
