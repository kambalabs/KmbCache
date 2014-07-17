<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\NodeStatisticsProxyFactory;
use KmbCacheTest\Bootstrap;

class NodeStatisticsProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new NodeStatisticsProxyFactory();

        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsProxy', $service);
        $this->assertInstanceOf('KmbCache\Service\CacheManager', $service->getCacheManager());
    }
}
