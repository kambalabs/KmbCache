<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\NodeStatisticsProxy;
use KmbCache\Service\NodeStatisticsProxyFactory;
use KmbCacheTest\Bootstrap;

class NodeStatisticsProxyFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new NodeStatisticsProxyFactory();

        /** @var NodeStatisticsProxy $service */
        $service = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsProxy', $service);
        $this->assertInstanceOf('KmbPuppetDb\Service\NodeStatistics', $service->getNodeStatisticsService());
        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $service->getCacheStorage());
    }
}
