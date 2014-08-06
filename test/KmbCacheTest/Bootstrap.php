<?php
namespace KmbCacheTest;

use KmbBaseTest\AbstractBootstrap;
use Zend\Stdlib\ArrayUtils;

define('BASE_PATH', dirname(dirname(__DIR__)));
$kmbBaseModulePath = BASE_PATH . '/vendor/kambalabs/kmb-base';
if (!is_dir($kmbBaseModulePath)) {
    $kmbBaseModulePath = dirname(BASE_PATH) . '/KmbBase';
}
require $kmbBaseModulePath . '/test/KmbBaseTest/AbstractBootstrap.php';

class Bootstrap extends AbstractBootstrap
{
    /**
     * Get the root path of the module.
     * Usually : dirname(dirname(__DIR__))
     *
     * @return string
     */
    public static function rootPath()
    {
        return BASE_PATH;
    }

    public static function getNamespacePaths()
    {
        return ArrayUtils::merge(parent::getNamespacePaths(), [
            'KmbPuppetDbTest' => dirname(dirname(dirname(__DIR__))) . '/KmbPuppetDb/test/KmbPuppetDbTest',
            __NAMESPACE__ => __DIR__
        ]);
    }

    public static function getApplicationConfig()
    {
        return ArrayUtils::merge(
            parent::getApplicationConfig(),
            [
                'module_listener_options' => [
                    'config_glob_paths' => [
                        dirname(__DIR__) . '/{,*.}{global,local}.php',
                    ],
                ],
                'modules' => [
                    'ZfcRbac',
                    'KmbDomain',
                    'KmbMemoryInfrastructure',
                    'KmbAuthentication',
                    'KmbFakeAuthentication',
                    'KmbPermission',
                    'KmbPuppetDb',
                    'KmbCache',
                ]
            ]
        );
    }
}

Bootstrap::init();
Bootstrap::chroot();
