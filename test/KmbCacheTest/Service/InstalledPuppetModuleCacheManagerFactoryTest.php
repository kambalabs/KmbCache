<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\InstalledPuppetModuleCacheManager;
use KmbCacheTest\Bootstrap;

class InstalledPuppetModuleCacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var InstalledPuppetModuleCacheManager $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\InstalledPuppetModuleCacheManager');

        $this->assertInstanceOf('KmbCache\Service\InstalledPuppetModuleCacheManager', $service);
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getPuppetModuleService());
    }
}
