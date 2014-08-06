<?php
namespace KmbCacheTest\Service;

use KmbCache\Service;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class ReportStatisticsProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StorageInterface
     */
    protected $cacheStorage;

    /**
     * @var Service\ReportStatisticsProxy
     */
    private $reportStatisticsProxyService;

    protected function setUp()
    {
        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $querySuffixBuilder = $this->getMock('KmbCache\Service\QuerySuffixBuilderInterface');
        $querySuffixBuilder->expects($this->any())
            ->method('build')
            ->will($this->returnCallback(function ($query) {
                if ($query instanceof Query) {
                    $query = $query->getData();
                }
                return empty($query) ? '' : '.' . $query[2];
            }));
        $reportStatisticsService = $this->getMock('KmbPuppetDb\Service\ReportStatistics');
        $reportStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnCallback(function ($query = null) {
                if ($query == ['=', 'environment', 'STABLE_PF1']) {
                    return [
                        'skips' => 1,
                        'success' => 1,
                        'failures' => 0,
                        'noops' => 2,
                    ];
                } elseif ($query !== null) {
                    return [
                        'skips' => 2,
                        'success' => 2,
                        'failures' => 1,
                        'noops' => 1,
                    ];
                }
                return [
                    'skips' => 3,
                    'success' => 2,
                    'failures' => 1,
                    'noops' => 5,
                ];
            }));
        $this->reportStatisticsProxyService = new Service\ReportStatisticsProxy();
        $this->reportStatisticsProxyService->setReportStatisticsService($reportStatisticsService);
        $this->reportStatisticsProxyService->setCacheStorage($this->cacheStorage);
        $this->reportStatisticsProxyService->setQuerySuffixBuilder($querySuffixBuilder);
    }

    /** @test */
    public function canGetAllAsArrayFromCache()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_REPORT_STATISTICS, ['skips' => 2]);

        $this->assertEquals(['skips' => 2], $this->reportStatisticsProxyService->getAllAsArray());
    }

    /** @test */
    public function canGetAllAsArray()
    {
        $expectedStats = [
            'skips' => 3,
            'success' => 2,
            'failures' => 1,
            'noops' => 5,
        ];

        $this->assertEquals($expectedStats, $this->reportStatisticsProxyService->getAllAsArray());
    }

    /** @test */
    public function canGetAllAsArrayWithQueryFromCache()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_REPORT_STATISTICS . '.STABLE_PF1', ['skips' => 1]);

        $reportStatistics = $this->reportStatisticsProxyService->getAllAsArray(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals(['skips' => 1], $reportStatistics);
    }

    /** @test */
    public function canGetAllAsArrayWithQuery()
    {
        $expectedStats = [
            'skips' => 1,
            'success' => 1,
            'failures' => 0,
            'noops' => 2,
        ];

        $reportStatistics = $this->reportStatisticsProxyService->getAllAsArray(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals($expectedStats, $reportStatistics);
    }

    /** @test */
    public function canGetAllAsArrayWithOtherQuery()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_REPORT_STATISTICS, ['skips' => 1]);

        $expectedStat = [
            'skips' => 2,
            'success' => 2,
            'failures' => 1,
            'noops' => 1,
        ];

        $reportStatistics = $this->reportStatisticsProxyService->getAllAsArray(['=', 'certname', 'node1.local']);

        $this->assertEquals($expectedStat, $reportStatistics);
    }

    /** @test */
    public function canGetSkips()
    {
        $this->assertEquals(3, $this->reportStatisticsProxyService->getSkipsCount());
    }

    /** @test */
    public function canGetSuccess()
    {
        $this->assertEquals(2, $this->reportStatisticsProxyService->getSuccessCount());
    }

    /** @test */
    public function canGetFailures()
    {
        $this->assertEquals(1, $this->reportStatisticsProxyService->getFailuresCount());
    }

    /** @test */
    public function canGetNoops()
    {
        $this->assertEquals(5, $this->reportStatisticsProxyService->getNoopsCount());
    }
}
