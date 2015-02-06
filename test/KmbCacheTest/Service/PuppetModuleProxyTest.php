<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\PuppetModuleProxy;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Model\PuppetModule;

class PuppetModuleProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var PuppetModuleProxy */
    protected $proxy;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var  PuppetModule[] */
    protected $expectedInstallableModules;

    /** @var  PuppetModule[] */
    protected $expectedInstalledModules;

    protected function setUp()
    {
        $parent = new Environment();
        $parent->setName('STABLE');
        $this->environment = new Environment();
        $this->environment->setName('PF1');
        $this->environment->setParent($parent);
        $this->proxy = new PuppetModuleProxy();
        $cacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $this->expectedInstalledModules = ['ntp' => new PuppetModule('ntp', '1.1.2')];
        $this->expectedInstallableModules = ['apache' => new PuppetModule('apache', '2.4.2'), 'mysql' => new PuppetModule('mysql', '5.5.3')];
        $cacheManager->expects($this->any())
            ->method('getInstalledPuppetModules')
            ->will($this->returnValue($this->expectedInstalledModules));
        $cacheManager->expects($this->any())
            ->method('getInstallablePuppetModules')
            ->will($this->returnValue($this->expectedInstallableModules));
        $this->proxy->setCacheManager($cacheManager);
    }

    /** @test */
    public function canGetAllInstallableByEnvironment()
    {
        $modules = $this->proxy->getAllInstallableByEnvironment($this->environment);

        $this->assertEquals($this->expectedInstallableModules, $modules);
    }

    /** @test */
    public function canGetAllInstalledByEnvironment()
    {
        $modules = $this->proxy->getAllInstalledByEnvironment($this->environment);

        $this->assertEquals($this->expectedInstalledModules, $modules);
    }

    /** @test */
    public function cannotGetUnknownModuleByEnvironmentAndName()
    {
        $module = $this->proxy->getInstalledByEnvironmentAndName($this->environment, 'unknown');

        $this->assertNull($module);
    }

    /** @test */
    public function canGetByEnvironmentAndName()
    {
        $module = $this->proxy->getInstalledByEnvironmentAndName($this->environment, 'ntp');

        $this->assertInstanceOf('KmbPmProxy\Model\PuppetModule', $module);
        $this->assertEquals('ntp', $module->getName());
        $this->assertEquals('1.1.2', $module->getVersion());
    }
}
