<?php
namespace KmbCacheTest\Service;

use KmbCache\Service;
use KmbPuppetDb\Model;

class ReportStatisticsProxyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Service\ReportStatisticsProxy
     */
    private $reportStatisticsProxyService;

    protected function setUp()
    {
        $cacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $cacheManager->expects($this->any())
            ->method('getItem')
            ->will($this->returnValue(array(
                'skips' => 3,
                'success' => 1,
                'failures' => 1,
                'noops' => 5,
            )));
        $this->reportStatisticsProxyService = new Service\ReportStatisticsProxy();
        $this->reportStatisticsProxyService->setCacheManager($cacheManager);
    }

    /** @test */
    public function canGetAllAsArray()
    {
        $this->assertEquals(array(
            'skips' => 3,
            'success' => 1,
            'failures' => 1,
            'noops' => 5,
        ), $this->reportStatisticsProxyService->getAllAsArray());
    }

    /** @test */
    public function canGetSkips()
    {
        $this->assertEquals(3, $this->reportStatisticsProxyService->getSkipsCount());
    }

    /** @test */
    public function canGetSuccess()
    {
        $this->assertEquals(1, $this->reportStatisticsProxyService->getSuccessCount());
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
