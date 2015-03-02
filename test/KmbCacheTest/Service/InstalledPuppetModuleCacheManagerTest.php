<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\AbstractCacheManager;
use KmbCache\Service\DefaultDataContextBuilder;
use KmbCache\Service\DefaultSuffixBuilder;
use KmbCache\Service\InstalledPuppetModuleCacheManager;
use KmbCacheTest\Bootstrap;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class InstalledPuppetModuleCacheManagerTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';
    const KEY = 'installedModules';

    /** @var \DateTime */
    protected $now;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var AbstractCacheManager */
    protected $cacheManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $puppetModuleService;

    protected function setUp()
    {
        $this->environment = new Environment('PF1');
        $this->environment->setParent(new Environment('STABLE'));

        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->now = new \DateTime(static::FAKE_DATETIME);

        $this->puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $this->puppetModuleService->expects($this->any())
            ->method('getAllInstalledByEnvironment')
            ->with($this->environment)
            ->will($this->returnValue(['foo', 'bar', 'baz']));

        $this->cacheManager = new InstalledPuppetModuleCacheManager();
        $this->cacheManager->setKey(static::KEY);
        $this->cacheManager->setCacheStorage($this->cacheStorage);
        $this->cacheManager->setDateTimeFactory(new FakeDateTimeFactory($this->now));
        $this->cacheManager->setLogger(Bootstrap::getServiceManager()->get('Logger'));
        $this->cacheManager->setSuffixBuilder(new DefaultSuffixBuilder());
        $this->cacheManager->setDataContextBuilder(new DefaultDataContextBuilder());
        $this->cacheManager->setPuppetModuleService($this->puppetModuleService);
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $lastMonth = new \DateTime(static::FAKE_DATETIME);
        $lastMonth->sub(\DateInterval::createFromDateString('1 month'));
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1.refreshedAt', serialize($lastMonth));
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1', serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertTrue($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1'));
        $this->assertEquals(AbstractCacheManager::COMPLETED, $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1.status'));
        $this->assertEquals(serialize($this->now), $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1.refreshedAt'));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenRefreshIsPending()
    {
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1.status', AbstractCacheManager::PENDING);
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1', serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertFalse($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo']), $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1'));
    }

    /** @test */
    public function canRefreshExpiredCacheWhenRefreshIsPendingButCacheIsEmpty()
    {
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1.status', AbstractCacheManager::PENDING);

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertTrue($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1'));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenNoCacheIsExpired()
    {
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1.refreshedAt', serialize($this->now));
        $this->cacheStorage->setItem(static::KEY . '.STABLE_PF1', serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache($this->environment);

        $this->assertFalse($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo']), $this->cacheStorage->getItem(static::KEY . '.STABLE_PF1'));
    }
}
