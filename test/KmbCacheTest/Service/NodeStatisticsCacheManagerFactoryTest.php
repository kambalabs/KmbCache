<?php
namespace KmbCacheTest\Service;

use KmbCacheTest\Bootstrap;
use KmbPuppetDb\Cache\NodeStatisticsCacheManager;

class NodeStatisticsCacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var NodeStatisticsCacheManager $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\NodeStatisticsCacheManager');

        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsCacheManager', $service);
        $this->assertInstanceOf('KmbCache\Service\PuppetDbQuerySuffixBuilder', $service->getSuffixBuilder());
        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsDataContextBuilder', $service->getDataContextBuilder());
        $this->assertInstanceOf('KmbPuppetDb\Service\NodeStatistics', $service->getNodeStatisticsService());
    }
}
