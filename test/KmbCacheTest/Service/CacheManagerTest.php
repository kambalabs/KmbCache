<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\CacheManager;
use KmbCacheTest\Bootstrap;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Model\PuppetModule;
use KmbPuppetDb\Model;
use KmbPuppetDb\Query\NodesV4EnvironmentsQueryBuilder;
use KmbPuppetDb\Query\Query;
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
    protected $pmProxyPuppetModuleService;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var CacheManager */
    protected $cacheManager;

    protected function setUp()
    {
        $this->nodeStatisticsService = $this->getMock('KmbPuppetDb\Service\NodeStatistics');
        $this->pmProxyPuppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
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
        $this->cacheManager->setDateTimeFactory(new FakeDateTimeFactory($this->now));
        $this->cacheManager->setQuerySuffixBuilder($querySuffixBuilder);
        $this->cacheManager->setNodesEnvironmentsQueryBuilder(new NodesV4EnvironmentsQueryBuilder());
        $this->cacheManager->setPermissionEnvironmentService($permissionEnvironmentService);
        $this->cacheManager->setPmProxyPuppetModuleService($this->pmProxyPuppetModuleService);
        $this->cacheManager->setLogger(Bootstrap::getServiceManager()->get('Logger'));
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $lastMonth = new \DateTime(static::FAKE_DATETIME);
        $lastMonth->sub(\DateInterval::createFromDateString('1 month'));
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS), $lastMonth);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);
        $expectedNodeStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodeStatistics));

        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals($expectedNodeStatistics, $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor(CacheManager::KEY_NODE_STATISTICS)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS)));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenRefreshIsPending()
    {
        $this->cacheStorage->setItem(CacheManager::statusKeyFor(CacheManager::KEY_NODE_STATISTICS), CacheManager::PENDING);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);

        $this->cacheManager->refreshExpiredCache();

        $this->assertEquals(['nodesCount' => 1], $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenNoCacheIsExpired()
    {
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor(CacheManager::KEY_NODE_STATISTICS), $this->now);
        $this->cacheStorage->setItem(CacheManager::KEY_NODE_STATISTICS, ['nodesCount' => 1]);

        $refresh = $this->cacheManager->refreshExpiredCache();

        $this->assertFalse($refresh);
        $this->assertEquals(['nodesCount' => 1], $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS));
    }

    /** @test */
    public function canRefreshExpiredWithEnvironment()
    {
        $nodesKey = CacheManager::KEY_NODE_STATISTICS . '.STABLE_PF1';
        $modulesKey = CacheManager::KEY_PUPPET_MODULES . 'STABLE_PF1';
        $this->cacheStorage->setItem($nodesKey, ['nodesCount' => 1]);
        $this->cacheStorage->setItem($modulesKey, ['ntp' => new PuppetModule('ntp', '1.1.2')]);
        $expectedNodeStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodeStatistics));
        $expectedModules = ['apache' => new PuppetModule('apache', '1.0.2')];
        $this->pmProxyPuppetModuleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue($expectedModules));

        $refresh = $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertTrue($refresh);
        $this->assertEquals($expectedNodeStatistics, $this->cacheStorage->getItem($nodesKey));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor($nodesKey)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor($nodesKey)));
        $this->assertEquals($expectedModules, $this->cacheStorage->getItem($modulesKey));
        $this->assertEquals(CacheManager::COMPLETED, $this->cacheStorage->getItem(CacheManager::statusKeyFor($modulesKey)));
        $this->assertEquals($this->now, $this->cacheStorage->getItem(CacheManager::refreshedAtKeyFor($modulesKey)));
    }

    /** @test */
    public function canClearCache()
    {
        $nodesKey = CacheManager::KEY_NODE_STATISTICS . '.STABLE_PF1';
        $modulesKey = CacheManager::KEY_PUPPET_MODULES . 'STABLE_PF1';
        $this->cacheStorage->setItem($nodesKey, ['nodesCount' => 1]);
        $this->cacheStorage->setItem(CacheManager::statusKeyFor($nodesKey), CacheManager::COMPLETED);
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor($nodesKey), $this->now);
        $this->cacheStorage->setItem($modulesKey, ['ntp' => new PuppetModule('ntp', '1.1.2')]);
        $this->cacheStorage->setItem(CacheManager::statusKeyFor($modulesKey), CacheManager::COMPLETED);
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor($modulesKey), $this->now);

        $this->cacheManager->clearCache($this->environment);

        $this->assertFalse($this->cacheStorage->hasItem($nodesKey));
        $this->assertFalse($this->cacheStorage->hasItem(CacheManager::statusKeyFor($nodesKey)));
        $this->assertFalse($this->cacheStorage->hasItem(CacheManager::refreshedAtKeyFor($nodesKey)));
        $this->assertFalse($this->cacheStorage->hasItem($modulesKey));
        $this->assertFalse($this->cacheStorage->hasItem(CacheManager::statusKeyFor($modulesKey)));
        $this->assertFalse($this->cacheStorage->hasItem(CacheManager::refreshedAtKeyFor($modulesKey)));
    }

    /** @test */
    public function canGetNodeStatistics()
    {
        $expectedNodeStatistics = ['nodesCount' => 2];
        $key = CacheManager::KEY_NODE_STATISTICS . '.STABLE_PF1';
        $this->cacheStorage->setItem($key, $expectedNodeStatistics);
        $this->cacheStorage->setItem(CacheManager::statusKeyFor($key), CacheManager::COMPLETED);
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor($key), $this->now);

        $nodeStatistics = $this->cacheManager->getNodeStatistics(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals($expectedNodeStatistics, $nodeStatistics);
    }

    /** @test */
    public function canGetNodeStatisticsFromRealService()
    {
        $expectedNodeStatistics = ['nodesCount' => 2];
        $this->nodeStatisticsService->expects($this->any())
            ->method('getAllAsArray')
            ->will($this->returnValue($expectedNodeStatistics));

        $nodeStatistics = $this->cacheManager->getNodeStatistics(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals($expectedNodeStatistics, $nodeStatistics);
    }

    /** @test */
    public function canGetModules()
    {
        $key = CacheManager::KEY_PUPPET_MODULES . 'STABLE_PF1';
        $expectedModules = ['ntp' => new PuppetModule('ntp', '1.1.2')];
        $this->cacheStorage->setItem($key, $expectedModules);
        $this->cacheStorage->setItem(CacheManager::statusKeyFor($key), CacheManager::COMPLETED);
        $this->cacheStorage->setItem(CacheManager::refreshedAtKeyFor($key), $this->now);

        $modules = $this->cacheManager->getPuppetModules($this->environment);

        $this->assertEquals($expectedModules, $modules);
    }

    /** @test */
    public function canGetModulesFromRealService()
    {
        $expectedModules = ['apache' => new PuppetModule('apache', '1.0.2')];
        $this->pmProxyPuppetModuleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue($expectedModules));

        $modules = $this->cacheManager->getPuppetModules($this->environment);

        $this->assertEquals($expectedModules, $modules);
    }
}
