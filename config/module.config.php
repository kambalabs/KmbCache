<?php
return array(
    'router' => array(
        'routes' => array(
            'cache' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/cache[/][:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'KmbCache\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),
    'translator' => array(
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'KmbCache\Controller\Index' => 'KmbCache\Controller\IndexController'
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'KmbCache\Service\CacheManager' => 'KmbCache\Service\CacheManagerFactory',
            'KmbPuppetDb\Service\NodeStatistics' => 'KmbCache\Service\NodeStatisticsProxyFactory',
            'KmbPuppetDb\Service\ReportStatistics' => 'KmbCache\Service\ReportStatisticsProxyFactory',
        ),
    ),
);
