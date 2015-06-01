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
    'controller_plugins' => [
        'factories' => [
            'translate' => 'KmbBaseTest\Controller\Plugin\FakeTranslateFactory',
            'translatePlural' => 'KmbBaseTest\Controller\Plugin\FakeTranslatePluralFactory',
        ],
    ],
    'service_manager' => [
        'invokables' => [
            'KmbCacheTest\Service\FakeCacheManager' => 'KmbCacheTest\Service\FakeCacheManager',
            'KmbCacheTest\Service\NoDescriptionCacheManager' => 'KmbCacheTest\Service\NoDescriptionCacheManager',
            'KmbCacheTest\Service\InvalidCacheManager' => 'KmbCacheTest\Service\InvalidCacheManager',
        ]
    ],
    'cache_manager' => [
        'nodeStatistics' => [
            'service' => 'KmbCacheTest\Service\FakeCacheManager',
        ],
        'availableModules' => [
            'service' => 'KmbCacheTest\Service\FakeCacheManager',
        ],
        'installableModules' => [
            'service' => 'KmbCacheTest\Service\FakeCacheManager',
        ],
        'installedModules' => [
            'service' => 'KmbCacheTest\Service\FakeCacheManager',
        ],
        'fake' => [
            'description' => 'Fake data',
            'service' => 'KmbCacheTest\Service\FakeCacheManager',
        ],
        'noDescription' => [
            'service' => 'KmbCacheTest\Service\NoDescriptionCacheManager',
        ],
        'noService' => [
            'description' => 'Fake data',
        ],
        'invalid' => [
            'description' => 'Fake data',
            'service' => 'KmbCacheTest\Service\InvalidCacheManager',
        ],
    ],
];
