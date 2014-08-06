<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\CacheManager;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\NodesV4EnvironmentsQueryBuilder;
use KmbPuppetDb\Query\Query;
use KmbPuppetDb\Query\ReportsV4EnvironmentsQueryBuilder;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';

    /** @var \DateTime */
    protected $now;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $nodeStatisticsService;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $reportStatisticsService;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var CacheManager */
    protected $cacheManager;

    protected function setUp()
    {
        $this->nodeStatisticsService = $this->getMock('KmbPuppetDb\Service\NodeStatistics');
        $this->reportStatisticsService = $this->getMock('KmbPuppetDb\Service\ReportStatistics');
        $querySuffixBuilder = $this->getMock('KmbCache\Service\QuerySuffixBuilderInterface');
        $querySuffixBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnCallback(function ($query) {
                if ($query instanceof Query) {
                    $query = $query->getData();
                }
                return empty($query) ? '' : '.' . $query[2];
            }));

        $parent = new Environment();
        $parent->setName('STABLE');
        $this->environment = new Environment();
        $this->environment->setName('PF1');
        $this->environment->setParent($parent);
        $permissionEnvironmentService = $this->getMock('KmbPermission\Service\Environment');
        $permissionEnvironmentService->expects($this->any())
            ->method('getAllReadable')
            ->will($this->returnCallback(function ($environment) {
                if ($environment) {
                    return [$this->environment];
                }
                return [];
            }));

        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->now = new \DateTime(static::FAKE_DATETIME);

        $this->cacheManager = new CacheManager();
        $this->cacheManager->setCacheStorage($this->cacheStorage);
        $this->cacheManager->setNodeStatisticsService($this->nodeStatisticsService);
        $this->cacheManager->setReportStatisticsService($this->reportStatisticsService);
        $this->cacheManager->setDateTimeFactory(new FakeDateTimeFactory($this->now));
        $this->cacheManager->setQuerySuffixBuilder($querySuffixBuilder);
        $this->cacheManager->setReportsEnvironmentsQueryBuilder(new ReportsV4EnvironmentsQueryBuilder());
        $this->cacheManager->setNodesEnvironmentsQueryBuilder(new NodesV4EnvironmentsQueryBuilder());
        $this->cacheManager->setPermissionEnvironmentService($permissionEnvironmentService);
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $lastMonth = new \DateTime(static::FAKE_DATETIME);
        $lastMonth->sub(\DateInterval::createFromDateString('1 month'));
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS), $lastMonth);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManager::KEY_REPORT_STATISTICS, ['skips' => 1]);
        $expectedNodesStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodesStatistics));
        $expectedReportsStatistics = ['failure' => 1, 'success' => 1];
        $this->reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedReportsStatistics));

        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals($expectedNodesStatistics, $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor(CacheManager::KEY_NODE_STATISTICS)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS)));
        $this->assertEquals($expectedReportsStatistics, $this->cacheStorage->getItem(CacheManager::KEY_REPORT_STATISTICS));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor(CacheManager::KEY_REPORT_STATISTICS)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_REPORT_STATISTICS)));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenRefreshIsPending()
    {
        $this->cacheStorage->setItem(CacheManager::statusKeyFor(CacheManager::KEY_NODE_STATISTICS), CacheManager::PENDING);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManager::statusKeyFor(CacheManager::KEY_REPORT_STATISTICS), CacheManager::PENDING);
        $this->cacheStorage->setItem(CacheManager::KEY_REPORT_STATISTICS, ['skips' => 1]);

        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals(['nodesCount' => 1], $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
        $this->assertEquals(['skips' => 1], $this->cacheStorage->getItem(CacheManager::KEY_REPORT_STATISTICS));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenNoCacheIsExpired()
    {
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS), $this->now);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_REPORT_STATISTICS), $this->now);
        $this->cacheStorage->setItem(CacheManager::KEY_REPORT_STATISTICS, ['skips' => 1]);

        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals(['nodesCount' => 1], $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
        $this->assertEquals(['skips' => 1], $this->cacheStorage->getItem(CacheManager::KEY_REPORT_STATISTICS));
    }

    /** @test */
    public function canRefresExpiredhWithQuery()
    {
        $nodesKey = CacheManager::KEY_NODE_STATISTICS . '.STABLE_PF1';
        $reportsKey = CacheManager::KEY_REPORT_STATISTICS . '.STABLE_PF1';
        $this->cacheStorage->setItem($nodesKey, ['nodesCount' => 1]);
        $this->cacheStorage->setItem($reportsKey, ['skips' => 1]);
        $expectedNodesStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodesStatistics));
        $expectedReportsStatistics = ['failure' => 1, 'success' => 1];
        $this->reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedReportsStatistics));

        $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertEquals($expectedNodesStatistics, $this->cacheStorage->getItem($nodesKey));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor($nodesKey)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor($nodesKey)));
        $this->assertEquals($expectedReportsStatistics, $this->cacheStorage->getItem($reportsKey));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor($reportsKey)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor($reportsKey)));
    }
}
