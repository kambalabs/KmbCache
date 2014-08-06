<?php
return [
    'router' => [
        'routes' => [
            'cache' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '[/env/:envId]/refresh-expired-cache',
                    'constraints' => [
                        'envId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => 'KmbCache\Controller\Index',
                        'action'     => 'refresh-expired-cache',
                        'envId' => '0',
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
        'invokables' => [
            'KmbCache\Service\QuerySuffixBuilder' => 'KmbCache\Service\QuerySuffixBuilder',
        ],
        'factories' => [
            'KmbCache\Service\CacheManager' => 'KmbCache\Service\CacheManagerFactory',
            'KmbCache\Service\NodeStatisticsProxy' => 'KmbCache\Service\NodeStatisticsProxyFactory',
            'KmbCache\Service\ReportStatisticsProxy' => 'KmbCache\Service\ReportStatisticsProxyFactory',
        ],
        'aliases' => [
            'nodeStatisticsService' => 'KmbCache\Service\NodeStatisticsProxy',
            'reportStatisticsService' => 'KmbCache\Service\ReportStatisticsProxy',
        ],
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\ControllerGuard' => [
                [
                    'controller' => 'KmbCache\Controller\Index',
                    'actions' => ['refresh-expired-cache'],
                    'roles' => ['user']
                ],
            ]
        ],
    ],
];
