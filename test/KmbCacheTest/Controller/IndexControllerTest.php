<?php
namespace KmbCacheTest\Controller;

use KmbCache\Service\CacheManagerInterface;
use KmbCacheTest\Bootstrap;
use KmbCore\FakeDateTimeFactory;
use KmbPuppetDb\Model;
use KmbPuppetDbTest\FakeHttpClient;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    const FAKE_DATETIME = '2014-01-31 10:00:00';
    protected $traceError = true;

    /**
     * @var StorageInterface
     */
    protected $cacheStorage;

    public function setUp()
    {
        $this->setApplicationConfig(Bootstrap::getApplicationConfig());
        parent::setUp();
        $this->cacheStorage = StorageFactory::factory(array('adapter' => array('name' => 'memory')));
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('DateTimeFactory', new FakeDateTimeFactory(new \DateTime(static::FAKE_DATETIME)));
        $serviceManager->setService('CacheService', $this->cacheStorage);
        $serviceManager->setService('KmbPuppetDb\Http\Client', new FakeHttpClient(static::FAKE_DATETIME));
    }

    /** @test */
    public function canGetIndex()
    {
        $now = new \DateTime();
        $this->cacheStorage->setItem('refreshedAt', $now);
        $this->cacheStorage->setItem('cacheStatus', CacheManagerInterface::PENDING);

        $this->dispatch('/cache');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertResponseHeaderContains('Content-Type', 'application/json; charset=utf-8');
        $this->assertEquals('{"refreshed_at":"' . $now->format(\DateTime::RFC1123) . '","status":"pending"}', $this->getResponse()->getContent());
    }

    /** @test */
    public function canRefreshCache()
    {
        $this->cacheStorage->setItem('nodesStatistics', array('nodesCount' => 1));

        $this->dispatch('/cache/refresh');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"message":"OK"}', $this->getResponse()->getContent());
        $this->assertEquals(
            array(
                'unchangedCount' => 5,
                'changedCount' => 2,
                'failedCount' => 2,
                'nodesCount' => 9,
                'nodesCountByOS' => array(
                    'Debian GNU/Linux 6.0.9 (squeeze)' => 2,
                    'Debian GNU/Linux 7.1 (wheezy)' => 2,
                    'Debian GNU/Linux 7.5 (wheezy)' => 1,
                    'Debian GNU/Linux 7.2 (wheezy)' => 1,
                    'Windows' => 1,
                    'Debian GNU/Linux 7.3 (wheezy)' => 1,
                    'Debian GNU/Linux 7.4 (wheezy)' => 1,
                ),
                'nodesPercentageByOS' => array(
                    'Debian GNU/Linux 6.0.9 (squeeze)' => 0.22,
                    'Debian GNU/Linux 7.1 (wheezy)' => 0.22,
                    'Debian GNU/Linux 7.5 (wheezy)' => 0.11,
                    'Debian GNU/Linux 7.2 (wheezy)' => 0.11,
                    'Windows' => 0.11,
                    'Debian GNU/Linux 7.3 (wheezy)' => 0.11,
                    'Debian GNU/Linux 7.4 (wheezy)' => 0.11,
                ),
                'osCount' => 7,
                'recentlyRebootedNodes' => array(
                    'node7.local' => '3:02 hours',
                    'node8.local' => '0:23 hours',
                ),
            ), $this->cacheStorage->getItem('nodesStatistics')
        );
    }

    /** @test */
    public function cannotRefreshCacheIfPending()
    {
        $this->cacheStorage->setItem('cacheStatus', CacheManagerInterface::PENDING);

        $this->dispatch('/cache/refresh');

        $this->assertResponseStatusCode(409);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"message":"Cache refresh is already in progress"}', $this->getResponse()->getContent());
    }
}
