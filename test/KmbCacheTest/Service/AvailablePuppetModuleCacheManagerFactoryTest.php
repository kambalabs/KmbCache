<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\AvailablePuppetModuleCacheManager;
use KmbCacheTest\Bootstrap;

class AvailablePuppetModuleCacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var AvailablePuppetModuleCacheManager $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\AvailablePuppetModuleCacheManager');

        $this->assertInstanceOf('KmbCache\Service\AvailablePuppetModuleCacheManager', $service);
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModule', $service->getPuppetModuleService());
    }
}
