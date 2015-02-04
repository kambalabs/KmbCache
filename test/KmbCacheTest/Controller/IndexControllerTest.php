<?php
namespace KmbCacheTest\Controller;

use KmbBase\FakeDateTimeFactory;
use KmbCache\Service\CacheManager;
use KmbCacheTest\Bootstrap;
use KmbMemoryInfrastructure\Fixtures;
use KmbPuppetDb\Model;
use KmbPuppetDbTest\FakeHttpClient;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
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
        $this->cacheStorage = StorageFactory::factory(['adapter' => ['name' => 'memory']]);
        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('DateTimeFactory', new FakeDateTimeFactory(new \DateTime(static::FAKE_DATETIME)));
        $serviceManager->setService('CacheService', $this->cacheStorage);
        $serviceManager->setService('KmbPuppetDb\Http\Client', new FakeHttpClient(static::FAKE_DATETIME));
        $pmProxyClient = $this->getMock('KmbPmProxy\Client');
        $pmProxyClient->expects($this->any())
            ->method('get')
            ->will($this->returnValue(json_decode(json_encode(['dns' => ['1.0.0'], 'apache' => ['0.1.0', '0.0.8']]))));
        $serviceManager->setService('KmbPmProxy\Client', $pmProxyClient);
    }

    /** @test */
    public function canRefreshExpiredCache()
    {
        $this->dispatch('/cache/refresh-expired');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"title":"Updating cache","message":"Cache data has been refreshed.","refresh":true}', $this->getResponse()->getContent());
        $this->assertEquals(
            [
                'unchangedCount' => 5,
                'changedCount' => 2,
                'failedCount' => 2,
                'nodesCount' => 9,
                'nodesCountByOS' => [
                    'Debian GNU/Linux 6.0.9 (squeeze)' => 2,
                    'Debian GNU/Linux 7.1 (wheezy)' => 2,
                    'Debian GNU/Linux 7.5 (wheezy)' => 1,
                    'Debian GNU/Linux 7.2 (wheezy)' => 1,
                    'Windows' => 1,
                    'Debian GNU/Linux 7.3 (wheezy)' => 1,
                    'Debian GNU/Linux 7.4 (wheezy)' => 1,
                ],
                'nodesPercentageByOS' => [
                    'Debian GNU/Linux 6.0.9 (squeeze)' => 0.22,
                    'Debian GNU/Linux 7.1 (wheezy)' => 0.22,
                    'Debian GNU/Linux 7.5 (wheezy)' => 0.11,
                    'Debian GNU/Linux 7.2 (wheezy)' => 0.11,
                    'Windows' => 0.11,
                    'Debian GNU/Linux 7.3 (wheezy)' => 0.11,
                    'Debian GNU/Linux 7.4 (wheezy)' => 0.11,
                ],
                'osCount' => 7,
                'recentlyRebootedNodes' => [
                    'node7.local' => '3:02 hours',
                    'node8.local' => '0:23 hours',
                ],
            ], $this->cacheStorage->getItem(CacheManager::KEY_NODE_STATISTICS)
        );
    }

    /** @test */
    public function canClearCache()
    {
        $this->dispatch('/cache/clear');

        $this->assertResponseStatusCode(200);
        $this->assertControllerName('KmbCache\Controller\Index');
        $this->assertEquals('{"message":"OK"}', $this->getResponse()->getContent());
        $this->assertFalse($this->cacheStorage->hasItem(CacheManager::KEY_NODE_STATISTICS)
        );
    }

    /**
     * @return ServiceManager
     */
    public function getServiceManager()
    {
        return $this->getApplicationServiceLocator();
    }
}
