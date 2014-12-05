<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\PuppetModuleProxy;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Model\PuppetModule;

class ModuleProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var PuppetModuleProxy */
    protected $proxy;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var  PuppetModule[] */
    protected $expectedModules;

    protected function setUp()
    {
        $parent = new Environment();
        $parent->setName('STABLE');
        $this->environment = new Environment();
        $this->environment->setName('PF1');
        $this->environment->setParent($parent);
        $this->proxy = new PuppetModuleProxy();
        $cacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $this->expectedModules = ['ntp' => new PuppetModule('ntp', '1.1.2')];
        $cacheManager->expects($this->any())
            ->method('getPuppetModules')
            ->will($this->returnValue($this->expectedModules));
        $this->proxy->setCacheManager($cacheManager);
    }

    /** @test */
    public function canGetAllByEnvironment()
    {
        $modules = $this->proxy->getAllInstalledByEnvironment($this->environment);

        $this->assertEquals($this->expectedModules, $modules);
    }

    /** @test */
    public function cannotGetUnknownModuleByEnvironmentAndName()
    {
        $module = $this->proxy->getByEnvironmentAndName($this->environment, 'unknown');

        $this->assertNull($module);
    }

    /** @test */
    public function canGetByEnvironmentAndName()
    {
        $module = $this->proxy->getByEnvironmentAndName($this->environment, 'ntp');

        $this->assertInstanceOf('KmbPmProxy\Model\PuppetModule', $module);
        $this->assertEquals('ntp', $module->getName());
        $this->assertEquals('1.1.2', $module->getVersion());
    }
}
