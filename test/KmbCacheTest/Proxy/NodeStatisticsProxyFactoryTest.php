<?php
namespace KmbCacheTest\Proxy;

use KmbCache\Proxy\NodeStatisticsProxy;
use KmbCacheTest\Bootstrap;

class NodeStatisticsProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var NodeStatisticsProxy $service */
        $service = Bootstrap::getServiceManager()->get('nodeStatisticsService');

        $this->assertInstanceOf('KmbCache\Proxy\NodeStatisticsProxy', $service);
        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsCacheManager', $service->getCacheManager());
    }
}
