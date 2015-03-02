<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\NodeStatisticsCacheManager;
use KmbCacheTest\Bootstrap;
use KmbDomain\Model\Environment;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class NodeStatisticsCacheManagerTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';
    const KEY = 'nodeStatistics';

    /** @var  StorageInterface */
    protected $cacheStorage;

    /** @var  NodeStatisticsCacheManager */
    protected $cacheManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $nodeStatisticsService;

    protected function setUp()
    {
        $now = new \DateTime(static::FAKE_DATETIME);
        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->nodeStatisticsService = $this->getMock('KmbPuppetDb\Service\NodeStatisticsInterface');
        $this->nodeStatisticsService->expects($this->any())->method('getAllAsArray')->will($this->returnCallback(function ($query) {
            if ($query == ['=', 'environment', 'STABLE']) {
                return ['nodesCount' => 2];
            }
            return ['nodesCount' => 5];
        }));
        $suffixBuilder = $this->getMock('KmbCache\Service\SuffixBuilderInterface');
        $suffixBuilder->expects($this->any())->method('build')->will($this->returnValue('.fake'));
        $contextBuilder = $this->getMock('KmbCache\Service\DataContextBuilderInterface');
        $contextBuilder->expects($this->any())->method('build')->will($this->returnCallback(function ($environment) {
            return $environment ? ['=', 'environment', 'STABLE'] : null;
        }));
        $this->cacheManager = new NodeStatisticsCacheManager();
        $this->cacheManager->setKey(self::KEY);
        $this->cacheManager->setCacheStorage($this->cacheStorage);
        $this->cacheManager->setDateTimeFactory(new FakeDateTimeFactory($now));
        $this->cacheManager->setLogger(Bootstrap::getServiceManager()->get('Logger'));
        $this->cacheManager->setSuffixBuilder($suffixBuilder);
        $this->cacheManager->setDataContextBuilder($contextBuilder);
        $this->cacheManager->setNodeStatisticsService($this->nodeStatisticsService);
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals(serialize(['nodesCount' => 5]), $this->cacheStorage->getItem(static::KEY . '.fake'));
    }

    /** @test */
    public function canRefreshExpiredCacheWithEnvironment()
    {
        $this->cacheManager->refreshExpiredCache(new Environment('STABLE'));

        $this->assertEquals(serialize(['nodesCount' => 2]), $this->cacheStorage->getItem(static::KEY . '.fake'));
    }
}
