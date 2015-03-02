<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\InstallablePuppetModuleCacheManager;
use KmbCacheTest\Bootstrap;

class InstallablePuppetModuleCacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var InstallablePuppetModuleCacheManager $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\InstallablePuppetModuleCacheManager');

        $this->assertInstanceOf('KmbCache\Service\InstallablePuppetModuleCacheManager', $service);
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getPuppetModuleService());
    }
}
