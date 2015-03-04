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
        $this->assertInstanceOf('KmbCache\Service\AvailablePuppetModuleCacheManager', $service->getAvailableModulesCacheManager());
        $this->assertInstanceOf('KmbCache\Service\InstallablePuppetModuleCacheManager', $service->getInstallableModulesCacheManager());
        $this->assertInstanceOf('KmbCache\Service\InstalledPuppetModuleCacheManager', $service->getInstalledModulesCacheManager());
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getModuleService());
    }
}
