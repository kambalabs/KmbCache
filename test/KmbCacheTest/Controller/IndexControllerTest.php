<?php
namespace KmbCacheTest\Controller;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\AbstractCacheManager;
use KmbCacheTest\Bootstrap;
use KmbMemoryInfrastructure\Fixtures;
use KmbPuppetDb\Model;
use Zend\Cache\Storage\StorageInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';

    use Fixtures;

    protected $traceError = true;

    /**
     * @var StorageInterface
     */
    protected $cacheStorage;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();
        $this->initFixtures();
        $serviceManager = $this->getServiceManager();
        $this->cacheStorage = $serviceManager->get('CacheService');
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('DateTimeFactory', new FakeDateTimeFactory(new \DateTime(static::FAKE_DATETIME)));
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $this->dispatch('/cache/refresh-expired');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"title":"Updating cache","message":"Cache data has been refreshed.","refresh":true}', $this->getResponse()->getContent());
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem('fake'));
    }

    /** @test */
    public function canRefreshCache()
    {
        $this->cacheStorage->setItem('fake', serialize(['foo']));
        $this->cacheStorage->setItem('fake.status', AbstractCacheManager::COMPLETED);
        $this->cacheStorage->setItem('fake.refreshedAt', new \DateTime(static::FAKE_DATETIME));
        $this->cacheStorage->setItem('noDescription', serialize(['bar']));
        $this->cacheStorage->setItem('noDescription.status', AbstractCacheManager::COMPLETED);
        $this->cacheStorage->setItem('noDescription.refreshedAt', new \DateTime(static::FAKE_DATETIME));

        $this->dispatch('/cache/refresh');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"message":"OK"}', $this->getResponse()->getContent());
        $this->assertEquals(serialize(['foo', 'bar', 'baz']), $this->cacheStorage->getItem('fake'));
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getApplicationServiceLocator();
    }
}
