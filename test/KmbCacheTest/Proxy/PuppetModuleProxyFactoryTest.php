<?php
namespace KmbCacheTest\Proxy;

use KmbCacheTest\Bootstrap;

class PuppetModuleProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var \KmbCache\Proxy\PuppetModuleProxy $service */
        $service = Bootstrap::getServiceManager()->get('pmProxyPuppetModuleService');

        $this->assertInstanceOf('KmbCache\Proxy\PuppetModuleProxy', $service);
        $this->assertInstanceOf('KmbCacheTest\Service\FakeCacheManager', $service->getAvailableModulesCacheManager());
        $this->assertInstanceOf('KmbCacheTest\Service\FakeCacheManager', $service->getInstallableModulesCacheManager());
        $this->assertInstanceOf('KmbCacheTest\Service\FakeCacheManager', $service->getInstalledModulesCacheManager());
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getModuleService());
    }
}
