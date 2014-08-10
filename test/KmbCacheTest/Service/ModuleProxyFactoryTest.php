<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\ModuleProxy;
use KmbCacheTest\Bootstrap;

class ModuleProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var ModuleProxy $service */
        $service = Bootstrap::getServiceManager()->get('pmProxyModuleService');

        $this->assertInstanceOf('KmbCache\Service\ModuleProxy', $service);
        $this->assertInstanceOf('KmbPmProxy\Service\Module', $service->getPmProxyModuleService());
        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $service->getCacheStorage());
    }
}