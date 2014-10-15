<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\PuppetModuleProxy;
use KmbCacheTest\Bootstrap;

class ModuleProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var PuppetModuleProxy $service */
        $service = Bootstrap::getServiceManager()->get('pmProxyPuppetModuleService');

        $this->assertInstanceOf('KmbCache\Service\PuppetModuleProxy', $service);
        $this->assertInstanceOf('KmbCache\Service\CacheManagerInterface', $service->getCacheManager());
    }
}
