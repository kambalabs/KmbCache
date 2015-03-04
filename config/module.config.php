<?php
return [
    'router' => [
        'routes' => [
            'cache' => [
                'type'    => 'Segment',
                'options' => [
                    'route'    => '[/env/:envId]/cache/:action',
                    'constraints' => [
                        'envId' => '[0-9]+',
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller' => 'KmbCache\Controller\Index',
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
        'factories' => [
            'KmbCache\Service\MainCacheManager' => 'KmbCache\Service\MainCacheManagerFactory',
            'KmbCache\Service\NodeStatisticsCacheManager' => 'KmbCache\Service\NodeStatisticsCacheManagerFactory',
            'KmbCache\Service\NodeStatisticsDataContextBuilder' => 'KmbCache\Service\NodeStatisticsDataContextBuilderFactory',
            'KmbCache\Service\AvailablePuppetModuleCacheManager' => 'KmbCache\Service\AvailablePuppetModuleCacheManagerFactory',
            'KmbCache\Service\InstallablePuppetModuleCacheManager' => 'KmbCache\Service\InstallablePuppetModuleCacheManagerFactory',
            'KmbCache\Service\InstalledPuppetModuleCacheManager' => 'KmbCache\Service\InstalledPuppetModuleCacheManagerFactory',
            'KmbCache\Proxy\NodeStatisticsProxy' => 'KmbCache\Proxy\NodeStatisticsProxyFactory',
            'KmbCache\Proxy\PuppetModuleProxy' => 'KmbCache\Proxy\PuppetModuleProxyFactory',
        ],
        'aliases' => [
            'nodeStatisticsService' => 'KmbCache\Proxy\NodeStatisticsProxy',
            'pmProxyPuppetModuleService' => 'KmbCache\Proxy\PuppetModuleProxy',
        ],
    ],
    'zfc_rbac' => [
        'guards' => [
            'ZfcRbac\Guard\ControllerGuard' => [
                [
                    'controller' => 'KmbCache\Controller\Index',
                    'actions' => ['refresh-expired', 'refresh'],
                    'roles' => ['user']
                ],
            ]
        ],
    ],
    'asset_manager' => [
        'resolver_configs' => [
            'paths' => [
                __DIR__ . '/../public',
            ],
        ],
    ],
    'cache_manager' => [
        'nodeStatistics' => [
            'description' => 'Servers statistics',
            'service' => 'KmbCache\Service\NodeStatisticsCacheManager',
        ],
        'availableModules' => [
            'description' => 'Available modules',
            'service' => 'KmbCache\Service\AvailablePuppetModuleCacheManager',
        ],
        'installableModules' => [
            'description' => 'Modules installable in environment',
            'service' => 'KmbCache\Service\InstallablePuppetModuleCacheManager',
        ],
        'installedModules' => [
            'description' => 'Modules installed in environment',
            'service' => 'KmbCache\Service\InstalledPuppetModuleCacheManager',
        ],
    ],
];
