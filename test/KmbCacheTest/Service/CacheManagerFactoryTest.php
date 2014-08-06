<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\CacheManager;
use KmbCache\Service\CacheManagerFactory;
use KmbCacheTest\Bootstrap;

class CacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new CacheManagerFactory();

        /** @var CacheManager $cacheManager */
        $cacheManager = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\CacheManager', $cacheManager);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $cacheManager->getCacheStorage());
        $this->assertInstanceOf('KmbPuppetDb\Service\NodeStatistics', $cacheManager->getNodeStatisticsService());
        $this->assertInstanceOf('KmbPuppetDb\Service\ReportStatistics', $cacheManager->getReportStatisticsService());
        $this->assertInstanceOf('KmbBase\DateTimeFactory', $cacheManager->getDateTimeFactory());
        $this->assertInstanceOf('KmbCache\Service\QuerySuffixBuilder', $cacheManager->getQuerySuffixBuilder());
        $this->assertInstanceOf('KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface', $cacheManager->getNodesEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPuppetDb\Query\EnvironmentsQueryBuilderInterface', $cacheManager->getReportsEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPermission\Service\Environment', $cacheManager->getPermissionEnvironmentService());
    }
}
