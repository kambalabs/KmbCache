<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\NodeStatisticsProxy;
use KmbCacheTest\Bootstrap;

class NodeStatisticsProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var NodeStatisticsProxy $service */
        $service = Bootstrap::getServiceManager()->get('nodeStatisticsService');

        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsProxy', $service);
        $this->assertInstanceOf('KmbCache\Service\CacheManagerInterface', $service->getCacheManager());
    }
}
