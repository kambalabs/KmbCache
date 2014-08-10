<?php
namespace KmbCacheTest\Service;

use KmbCache\Service;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\Query;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class NodeStatisticsProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var Service\NodeStatisticsProxy */
    protected $nodeStatisticsProxyService;

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
        $nodeStatisticsService = $this->getMock('KmbPuppetDb\Service\NodeStatistics');
        $nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnCallback(function ($query = null) {
                if ($query == ['=', 'facts-environment', 'STABLE_PF1']) {
                    return [
                        'unchangedCount' => 2,
                        'changedCount' => 0,
                        'failedCount' => 1,
                        'nodesCount' => 3,
                        'nodesCountByOS' => [
                            'Debian GNU/Linux 6.0.7 (squeeze)' => 1,
                            'windows' => 1,
                            'Debian GNU/Linux 7.4 (wheezy)' => 1,
                        ],
                        'nodesPercentageByOS' => [
                            'Debian GNU/Linux 6.0.7 (squeeze)' => 0.33,
                            'windows' => 0.33,
                            'Debian GNU/Linux 7.4 (wheezy)' => 0.33,
                        ],
                        'osCount' => 3,
                        'recentlyRebootedNodes' => ['node2.local' => '2:32 hours'],
                    ];
                } elseif ($query !== null) {
                    return [
                        'unchangedCount' => 1,
                        'changedCount' => 1,
                        'failedCount' => 0,
                        'nodesCount' => 2,
                        'nodesCountByOS' => [
                            'windows' => 1,
                            'Debian GNU/Linux 7.4 (wheezy)' => 1,
                        ],
                        'nodesPercentageByOS' => [
                            'windows' => 0.33,
                            'Debian GNU/Linux 7.4 (wheezy)' => 0.33,
                        ],
                        'osCount' => 2,
                        'recentlyRebootedNodes' => ['node2.local' => '2:32 hours'],
                    ];
                }
                return [
                    'unchangedCount' => 3,
                    'changedCount' => 1,
                    'failedCount' => 1,
                    'nodesCount' => 5,
                    'nodesCountByOS' => [
                        'Debian GNU/Linux 6.0.7 (squeeze)' => 2,
                        'windows' => 2,
                        'Debian GNU/Linux 7.4 (wheezy)' => 1,
                    ],
                    'nodesPercentageByOS' => [
                        'Debian GNU/Linux 6.0.7 (squeeze)' => 0.40,
                        'windows' => 0.40,
                        'Debian GNU/Linux 7.4 (wheezy)' => 0.20,
                    ],
                    'osCount' => 3,
                    'recentlyRebootedNodes' => ['node2.local' => '2:32 hours', 'node4.local' => '4:01 hours'],
                ];
            }));
        $this->nodeStatisticsProxyService = new Service\NodeStatisticsProxy();
        $this->nodeStatisticsProxyService->setNodeStatisticsService($nodeStatisticsService);
        $this->nodeStatisticsProxyService->setCacheStorage($this->cacheStorage);
        $this->nodeStatisticsProxyService->setQuerySuffixBuilder($querySuffixBuilder);
    }

    /** @test */
    public function canGetAllAsArrayFromCache()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);

        $this->assertEquals(['nodesCount' => 1], $this->nodeStatisticsProxyService->getAllAsArray());
    }

    /** @test */
    public function canGetAllAsArray()
    {
        $expectedStats = [
            'unchangedCount' => 3,
            'changedCount' => 1,
            'failedCount' => 1,
            'nodesCount' => 5,
            'nodesCountByOS' => [
                'Debian GNU/Linux 6.0.7 (squeeze)' => 2,
                'windows' => 2,
                'Debian GNU/Linux 7.4 (wheezy)' => 1,
            ],
            'nodesPercentageByOS' => [
                'Debian GNU/Linux 6.0.7 (squeeze)' => 0.40,
                'windows' => 0.40,
                'Debian GNU/Linux 7.4 (wheezy)' => 0.20,
            ],
            'osCount' => 3,
            'recentlyRebootedNodes' => ['node2.local' => '2:32 hours', 'node4.local' => '4:01 hours'],
        ];

        $this->assertEquals($expectedStats, $this->nodeStatisticsProxyService->getAllAsArray());
    }

    /** @test */
    public function canGetAllAsArrayWithQueryFromCache()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_NODE_STATISTICS . '.STABLE_PF1', ['nodesCount' => 1]);

        $nodeStatistics = $this->nodeStatisticsProxyService->getAllAsArray(['=', 'facts-environment', 'STABLE_PF1']);

        $this->assertEquals(['nodesCount' => 1], $nodeStatistics);
    }

    /** @test */
    public function canGetAllAsArrayWithQuery()
    {
        $expectedStats = [
            'unchangedCount' => 2,
            'changedCount' => 0,
            'failedCount' => 1,
            'nodesCount' => 3,
            'nodesCountByOS' => [
                'Debian GNU/Linux 6.0.7 (squeeze)' => 1,
                'windows' => 1,
                'Debian GNU/Linux 7.4 (wheezy)' => 1,
            ],
            'nodesPercentageByOS' => [
                'Debian GNU/Linux 6.0.7 (squeeze)' => 0.33,
                'windows' => 0.33,
                'Debian GNU/Linux 7.4 (wheezy)' => 0.33,
            ],
            'osCount' => 3,
            'recentlyRebootedNodes' => ['node2.local' => '2:32 hours'],
        ];

        $nodeStatistics = $this->nodeStatisticsProxyService->getAllAsArray(['=', 'facts-environment', 'STABLE_PF1']);

        $this->assertEquals($expectedStats, $nodeStatistics);
    }

    /** @test */
    public function canGetAllAsArrayWithOtherQuery()
    {
        $this->cacheStorage->setItem(Service\CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);

        $expectedStat = [
            'unchangedCount' => 1,
            'changedCount' => 1,
            'failedCount' => 0,
            'nodesCount' => 2,
            'nodesCountByOS' => [
                'windows' => 1,
                'Debian GNU/Linux 7.4 (wheezy)' => 1,
            ],
            'nodesPercentageByOS' => [
                'windows' => 0.33,
                'Debian GNU/Linux 7.4 (wheezy)' => 0.33,
            ],
            'osCount' => 2,
            'recentlyRebootedNodes' => ['node2.local' => '2:32 hours'],
        ];

        $nodeStatistics = $this->nodeStatisticsProxyService->getAllAsArray(['=', ['fact', 'operatingsystem'], 'Debian']);

        $this->assertEquals($expectedStat, $nodeStatistics);
    }

    /** @test */
    public function canGetUnchangedCount()
    {
        $this->assertEquals(3, $this->nodeStatisticsProxyService->getUnchangedCount());
    }

    /** @test */
    public function canGetChangedCount()
    {
        $this->assertEquals(1, $this->nodeStatisticsProxyService->getChangedCount());
    }

    /** @test */
    public function canGetFailedCount()
    {
        $this->assertEquals(1, $this->nodeStatisticsProxyService->getFailedCount());
    }

    /** @test */
    public function canGetNodesCount()
    {
        $this->assertEquals(5, $this->nodeStatisticsProxyService->getNodesCount());
    }

    /** @test */
    public function canGetOSCount()
    {
        $this->assertEquals(3, $this->nodeStatisticsProxyService->getOSCount());
    }

    /** @test */
    public function canGetNodesCountByOS()
    {
        $this->assertEquals([
            'Debian GNU/Linux 6.0.7 (squeeze)' => 2,
            'windows' => 2,
            'Debian GNU/Linux 7.4 (wheezy)' => 1,
        ], $this->nodeStatisticsProxyService->getNodesCountByOS());
    }

    /** @test */
    public function canGetNodesPercentageByOS()
    {
        $this->assertEquals([
            'Debian GNU/Linux 6.0.7 (squeeze)' => 0.40,
            'windows' => 0.40,
            'Debian GNU/Linux 7.4 (wheezy)' => 0.20,
        ], $this->nodeStatisticsProxyService->getNodesPercentageByOS());
    }

    /** @test */
    public function canGetRecentlyRebootedNodes()
    {
        $this->assertEquals([
            'node2.local' => '2:32 hours',
            'node4.local' => '4:01 hours'
        ], $this->nodeStatisticsProxyService->getRecentlyRebootedNodes());
    }
}
