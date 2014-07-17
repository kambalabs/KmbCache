<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\CacheManagerFactory;
use KmbCacheTest\Bootstrap;

class CacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new CacheManagerFactory();

        $cacheManager = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\CacheManager', $cacheManager);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $cacheManager->getCacheStorage());
        $this->assertInstanceOf('KmbPuppetDb\Service\NodeStatistics', $cacheManager->getNodeStatisticsService());
        $this->assertInstanceOf('KmbPuppetDb\Service\ReportStatistics', $cacheManager->getReportStatisticsService());
        $this->assertInstanceOf('KmbCore\DateTimeFactory', $cacheManager->getDateTimeFactory());
    }
}
