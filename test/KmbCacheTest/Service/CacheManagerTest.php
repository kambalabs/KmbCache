<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\CacheManagerInterface;
use KmbDomain\Model\Environment;
use KmbPuppetDb\Model;
use KmbCache\Exception\RuntimeException;
use KmbCache\Service\CacheManager;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';

    /**
     * @var StorageInterface
     */
    protected $cacheStorage;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $nodeStatisticsService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $reportStatisticsService;

    /**
     * @var CacheManager
     */
    protected $cacheManager;

    protected function setUp()
    {
        $this->nodeStatisticsService = $this->getMock('KmbPuppetDb\Service\NodeStatistics');
        $this->reportStatisticsService = $this->getMock('KmbPuppetDb\Service\ReportStatistics');
        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->cacheManager = new CacheManager();
        $this->cacheManager->setCacheStorage($this->cacheStorage);
        $this->cacheManager->setNodeStatisticsService($this->nodeStatisticsService);
        $this->cacheManager->setReportStatisticsService($this->reportStatisticsService);
        $this->cacheManager->setDateTimeFactory(new FakeDateTimeFactory(new \DateTime(static::FAKE_DATETIME)));
    }

    /** @test */
    public function canGetNullStatusWhenCacheIsEmpty()
    {
        $this->assertNull($this->cacheManager->getStatus());
    }

    /** @test */
    public function canGetStatus()
    {
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_CACHE_STATUS, CacheManagerInterface::PENDING);

        $this->assertEquals(CacheManagerInterface::PENDING, $this->cacheManager->getStatus());
    }

    /** @test */
    public function canGetNullRefreshedAtWhenCacheIsEmpty()
    {
        $this->assertNull($this->cacheManager->getRefreshedAt());
    }

    /** @test */
    public function canGetRefreshedAt()
    {
        $now = new \DateTime();
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_REFRESHED_AT, $now);

        $this->assertEquals($now, $this->cacheManager->getRefreshedAt());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cache refresh is already in progress
     */
    public function cannotRefreshWhenCacheStatusIsPending()
    {
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_CACHE_STATUS, CacheManagerInterface::PENDING);

        $this->cacheManager->refresh();
    }

    /** @test */
    public function canRefresh()
    {
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_NODE_STATISTICS, ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_REPORT_STATISTICS, ['skips' => 1]);
        $expectedNodesStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodesStatistics));
        $expectedReportsStatistics = ['failure' => 1, 'success' => 1];
        $this->reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedReportsStatistics));

        $this->cacheManager->refresh();

        $this->assertEquals($expectedNodesStatistics, $this->cacheStorage->getItem(CacheManagerInterface::KEY_NODE_STATISTICS));
        $this->assertEquals($expectedReportsStatistics, $this->cacheStorage->getItem(CacheManagerInterface::KEY_REPORT_STATISTICS));
        $this->assertEquals(CacheManagerInterface::COMPLETED, $this->cacheStorage->getItem(CacheManagerInterface::KEY_CACHE_STATUS));
        $this->assertEquals(new \DateTime(static::FAKE_DATETIME), $this->cacheStorage->getItem(CacheManagerInterface::KEY_REFRESHED_AT));
    }

    /** @test */
    public function canForceRefreshWhenCacheStatusIsPending()
    {
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_CACHE_STATUS, CacheManagerInterface::PENDING);

        $this->cacheManager->forceRefresh();
    }

    /** @test */
    public function canRefreshForSpecificEnvironment()
    {
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_NODE_STATISTICS . '.STABLE_PF1', ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManagerInterface::KEY_REPORT_STATISTICS . '.STABLE_PF1', ['skips' => 1]);
        $expectedNodesStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->with(['=', ['fact', Model\NodeInterface::ENVIRONMENT_FACT], 'STABLE_PF1'])
            ->will($this->returnValue($expectedNodesStatistics));
        $expectedReportsStatistics = ['failure' => 1, 'success' => 1];
        $this->reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->with(['=', 'environment', 'STABLE_PF1'])
            ->will($this->returnValue($expectedReportsStatistics));
        $parent = new Environment();
        $parent->setName('STABLE');
        $environment = new Environment();
        $environment->setName('PF1');
        $environment->setParent($parent);

        $this->cacheManager->refresh($environment);

        $this->assertEquals($expectedNodesStatistics, $this->cacheStorage->getItem(CacheManagerInterface::KEY_NODE_STATISTICS . '.STABLE_PF1'));
        $this->assertEquals($expectedReportsStatistics, $this->cacheStorage->getItem(CacheManagerInterface::KEY_REPORT_STATISTICS . '.STABLE_PF1'));
        $this->assertEquals(CacheManagerInterface::COMPLETED, $this->cacheStorage->getItem(CacheManagerInterface::KEY_CACHE_STATUS . '.STABLE_PF1'));
        $this->assertEquals(new \DateTime(static::FAKE_DATETIME), $this->cacheStorage->getItem(CacheManagerInterface::KEY_REFRESHED_AT . '.STABLE_PF1'));
    }
}
