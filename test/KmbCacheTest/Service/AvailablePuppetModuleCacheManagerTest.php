<?php
namespace KmbCacheTest\Service;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\AbstractCacheManager;
use KmbCache\Service\AvailablePuppetModuleCacheManager;
use KmbCache\Service\DefaultDataContextBuilder;
use KmbCache\Service\DefaultSuffixBuilder;
use KmbCacheTest\Bootstrap;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class AvailablePuppetModuleCacheManagerTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';
    const KEY = 'availableModules';

    /** @var \DateTime */
    protected $now;

    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var AbstractCacheManager */
    protected $cacheManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $puppetModuleService;

    protected function setUp()
    {
        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->now = new \DateTime(static::FAKE_DATETIME);

        $this->puppetModuleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $this->puppetModuleService->expects($this->any())->method('getAllAvailable')->will($this->returnValue(['foo', 'bar', 'baz']));

        $this->cacheManager = new AvailablePuppetModuleCacheManager();
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
        $this->cacheStorage->setItem(static::KEY . '.refreshedAt', serialize($lastMonth));
        $this->cacheStorage->setItem(static::KEY, serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache();

        $this->assertTrue($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem(static::KEY));
        $this->assertEquals(AbstractCacheManager::COMPLETED, $this->cacheStorage->getItem(static::KEY . '.status'));
        $this->assertEquals(serialize($this->now), $this->cacheStorage->getItem(static::KEY . '.refreshedAt'));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenRefreshIsPending()
    {
        $this->cacheStorage->setItem(static::KEY . '.status', AbstractCacheManager::PENDING);
        $this->cacheStorage->setItem(static::KEY, serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache();

        $this->assertFalse($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo']), $this->cacheStorage->getItem(static::KEY));
    }

    /** @test */
    public function canRefreshExpiredCacheWhenRefreshIsPendingButCacheIsEmpty()
    {
        $this->cacheStorage->setItem(static::KEY . '.status', AbstractCacheManager::PENDING);

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache();

        $this->assertTrue($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem(static::KEY));
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenNoCacheIsExpired()
    {
        $this->cacheStorage->setItem(static::KEY . '.refreshedAt', serialize($this->now));
        $this->cacheStorage->setItem(static::KEY, serialize(['foo']));

        $hasBeenRefreshed = $this->cacheManager->refreshExpiredCache();

        $this->assertFalse($hasBeenRefreshed);
        $this->assertEquals(serialize(['foo']), $this->cacheStorage->getItem(static::KEY));
    }
}
