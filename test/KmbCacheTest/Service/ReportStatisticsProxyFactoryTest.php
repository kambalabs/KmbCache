<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\ReportStatisticsProxyFactory;
use KmbCacheTest\Bootstrap;

class ReportStatisticsProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new ReportStatisticsProxyFactory();

        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\ReportStatisticsProxy', $service);
        $this->assertInstanceOf('KmbCache\Service\CacheManager', $service->getCacheManager());
    }
}