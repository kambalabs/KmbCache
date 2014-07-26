<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
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
        $this->cacheStorage->setItem('cacheStatus', CacheManager::PENDING);

        $this->assertEquals(CacheManager::PENDING, $this->cacheManager->getStatus());
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
        $this->cacheStorage->setItem('refreshedAt', $now);

        $this->assertEquals($now, $this->cacheManager->getRefreshedAt());
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage Cache refresh is already in progress
     */
    public function cannotRefreshWhenCacheStatusIsPending()
    {
        $this->cacheStorage->setItem('cacheStatus', CacheManager::PENDING);

        $this->cacheManager->refresh();
    }

    /** @test */
    public function canRefresh()
    {
        $this->cacheStorage->setItem('nodesStatistics', ['nodesCount' => 1]);
        $this->cacheStorage->setItem('reports', [new Model\Report(Model\ReportInterface::SUCCESS)]);
        $this->cacheStorage->setItem('reportsStatistics', ['skips' => 1]);
        $expectedNodesStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodesStatistics));
        $expectedReportsStatistics = ['failure' => 1, 'success' => 1];
        $this->reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedReportsStatistics));

        $this->cacheManager->refresh();

        $this->assertEquals($expectedNodesStatistics, $this->cacheStorage->getItem('nodesStatistics'));
        $this->assertEquals($expectedReportsStatistics, $this->cacheStorage->getItem('reportsStatistics'));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem('cacheStatus'));
        $this->assertEquals(new \DateTime(static::FAKE_DATETIME), $this->cacheStorage->getItem('refreshedAt'));
    }

    /** @test */
    public function canForceRefreshWhenCacheStatusIsPending()
    {
        $this->cacheStorage->setItem('cacheStatus', CacheManager::PENDING);

        $this->cacheManager->refresh(true);
    }

    /** @test */
    public function canGetItem()
    {
        $expectedNodesStatistics = [
            'unchangedCount' => 2,
            'changedCount' => 1,
            'failedCount' => 1,
            'nodesCount' => 9,
            'osCount' => 2,
        ];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodesStatistics));

        $this->assertEquals($expectedNodesStatistics, $this->cacheManager->getItem('nodesStatistics'));
    }

    /** @test */
    public function canGetItemFromCache()
    {
        $expectedNodesStatistics = [
            'unchangedCount' => 2,
            'changedCount' => 1,
            'failedCount' => 1,
            'nodesCount' => 9,
            'osCount' => 2,
        ];
        $this->cacheStorage->setItem('nodesStatistics', $expectedNodesStatistics);

        $this->assertEquals($expectedNodesStatistics, $this->cacheManager->getItem('nodesStatistics'));
    }
}
