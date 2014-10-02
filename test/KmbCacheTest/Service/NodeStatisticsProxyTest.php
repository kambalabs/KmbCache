<?php
namespace KmbCacheTest\Service;

use KmbCache\Service;
use KmbPuppetDb\Model;

class NodeStatisticsProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var Service\NodeStatisticsProxy */
    protected $nodeStatisticsProxyService;

    protected function setUp()
    {
        $cacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $cacheManager->expects($this->any())
            ->method('getNodesStatistics')
            ->will($this->returnCallback(function ($query = null) {
                if ($query === null) {
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
                }
                return ['nodesCount' => 2];
            }));

        $this->nodeStatisticsProxyService = new Service\NodeStatisticsProxy();
        $this->nodeStatisticsProxyService->setCacheManager($cacheManager);
    }

    /** @test */
    public function canGetAllAsArray()
    {
        $this->assertEquals(['nodesCount' => 2], $this->nodeStatisticsProxyService->getAllAsArray(['=', 'facts-environment', 'STABLE_PF1']));
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
