<?php
return [
    'router' => [
        'routes' => [
            'cache' => [
                'type'    => 'segment',
                'options' => [
                    'route'    => '/cache[/][:action][/:id]',
                    'constraints' => [
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbCache\Controller\Index',
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            'KmbCache\Controller\Index' => 'KmbCache\Controller\IndexController'
        ],
    ],
    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'abstract_factories' => [
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ],
        'factories' => [
            'KmbCache\Service\CacheManager' => 'KmbCache\Service\CacheManagerFactory',
            'KmbPuppetDb\Service\NodeStatistics' => 'KmbCache\Service\NodeStatisticsProxyFactory',
            'KmbPuppetDb\Service\ReportStatistics' => 'KmbCache\Service\ReportStatisticsProxyFactory',
        ],
    ],
];
