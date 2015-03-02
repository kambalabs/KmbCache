<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\MainCacheManager;

class MainCacheManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var  MainCacheManager */
    protected $mainCacheManager;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager1;

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $cacheManager2;

    protected function setUp()
    {
        $this->cacheManager1 = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $this->cacheManager2 = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $this->mainCacheManager = new MainCacheManager();
        $this->mainCacheManager->addCacheManager('fake1', $this->cacheManager1);
        $this->mainCacheManager->addCacheManager('fake2', $this->cacheManager2);
    }

    /** @test */
    public function canRefreshExpiredCacheWhenAtLeastOnCacheIsExpired()
    {
        $this->cacheManager1->expects($this->any())->method('refreshExpiredCache')->will($this->returnValue(true));
        $this->cacheManager2->expects($this->any())->method('refreshExpiredCache')->will($this->returnValue(false));

        $this->assertTrue($this->mainCacheManager->refreshExpiredCache());
    }

    /** @test */
    public function cannotRefreshExpiredCacheWhenNoCacheIsExpired()
    {
        $this->cacheManager1->expects($this->any())->method('refreshExpiredCache')->will($this->returnValue(false));
        $this->cacheManager2->expects($this->any())->method('refreshExpiredCache')->will($this->returnValue(false));

        $this->assertFalse($this->mainCacheManager->refreshExpiredCache());
    }

    /** @test */
    public function cannotGetUnknownCacheManager()
    {
        $this->assertNull($this->mainCacheManager->getCacheManager('unknown'));
    }

    /** @test */
    public function canGetCacheManager()
    {
        $this->assertEquals($this->cacheManager1, $this->mainCacheManager->getCacheManager('fake1'));
    }
}
