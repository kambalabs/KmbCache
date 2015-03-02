<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\MainCacheManager;
use KmbCacheTest\Bootstrap;

class MainCacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var MainCacheManager $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\MainCacheManager');

        $this->assertInstanceOf('KmbCache\Service\MainCacheManager', $service);
        $fakeCacheManager = $service->getCacheManager('fake');
        $this->assertInstanceOf('KmbCacheTest\Service\FakeCacheManager', $fakeCacheManager);
        $this->assertInstanceOf('Zend\Log\Logger', $fakeCacheManager->getLogger());
        $this->assertInstanceOf('Zend\Cache\Storage\StorageInterface', $fakeCacheManager->getCacheStorage());
        $this->assertInstanceOf('KmbBase\DateTimeFactoryInterface', $fakeCacheManager->getDateTimeFactory());
        $this->assertInstanceOf('KmbCache\Service\DefaultSuffixBuilder', $fakeCacheManager->getSuffixBuilder());
        $this->assertInstanceOf('KmbCache\Service\DefaultDataContextBuilder', $fakeCacheManager->getDataContextBuilder());
        $this->assertEquals('fake', $fakeCacheManager->getKey());
        $this->assertEquals('Fake data', $fakeCacheManager->getDescription());
        $this->assertEquals('noDescription', $service->getCacheManager('noDescription')->getDescription());
    }
}
