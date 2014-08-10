<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\CacheManager;
use KmbCache\Service\ModuleProxy;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Model\Module;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;

class ModuleProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var StorageInterface */
    protected $cacheStorage;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $moduleService;

    /** @var ModuleProxy */
    protected $proxy;

    /** @var EnvironmentInterface */
    protected $environment;

    protected function setUp()
    {
        $this->cacheStorage = StorageFactory::factory(['adapter' => 'memory']);
        $this->moduleService = $this->getMock('KmbPmProxy\Service\ModuleInterface');
        $parent = new Environment();
        $parent->setName('STABLE');
        $this->environment = new Environment();
        $this->environment->setName('PF1');
        $this->environment->setParent($parent);
        $this->proxy = new ModuleProxy();
        $this->proxy->setPmProxyModuleService($this->moduleService);
        $this->proxy->setCacheStorage($this->cacheStorage);
    }

    /** @test */
    public function canGetAllByEnvironmentFromCache()
    {
        $expectedModules = [ new Module('ntp', '1.0.0') ];
        $this->cacheStorage->setItem(CacheManager::KEY_MODULES . 'STABLE_PF1', $expectedModules);

        $modules = $this->proxy->getAllByEnvironment($this->environment);

        $this->assertEquals($expectedModules, $modules);
    }

    /** @test */
    public function canGetAllByEnvironmentWhenCacheIsEmpty()
    {
        $expectedModules = [
            new Module('apache', '2.1.3'),
            new Module('mysql', '0.2.8'),
        ];
        $this->moduleService->expects($this->any())
            ->method('getAllByEnvironment')
            ->will($this->returnValue($expectedModules));

        $modules = $this->proxy->getAllByEnvironment($this->environment);

        $this->assertEquals($expectedModules, $modules);
    }
}
