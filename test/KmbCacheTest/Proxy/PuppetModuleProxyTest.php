<?php
namespace KmbCacheTest\Proxy;

use KmbCache\Proxy\PuppetModuleProxy;
use KmbDomain\Model\Environment;
use KmbDomain\Model\EnvironmentInterface;
use KmbPmProxy\Model\PuppetModule;

class PuppetModuleProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var \KmbCache\Proxy\PuppetModuleProxy */
    protected $proxy;

    /** @var EnvironmentInterface */
    protected $environment;

    /** @var  PuppetModule[] */
    protected $expectedAvailableModules;

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
        $this->proxy = new \KmbCache\Proxy\PuppetModuleProxy();
        $this->expectedAvailableModules = ['apache' => new PuppetModule('apache', '2.4.2'), 'mysql' => new PuppetModule('mysql', '7.1.2'), 'tomcat' => new PuppetModule('tomcat', '7.1.2')];
        $this->expectedInstalledModules = ['ntp' => new PuppetModule('ntp', '1.1.2')];
        $this->expectedInstallableModules = ['apache' => new PuppetModule('apache', '2.4.2'), 'mysql' => new PuppetModule('mysql', '5.5.3')];
        $moduleService = $this->getMock('KmbPmProxy\Service\PuppetModuleInterface');
        $this->proxy->setModuleService($moduleService);
        $availableModulesCacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $availableModulesCacheManager->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->expectedAvailableModules));
        $this->proxy->setAvailableModulesCacheManager($availableModulesCacheManager);
        $installableModulesCacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $installableModulesCacheManager->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->expectedInstallableModules));
        $this->proxy->setInstallableModulesCacheManager($installableModulesCacheManager);
        $installedModulesCacheManager = $this->getMock('KmbCache\Service\CacheManagerInterface');
        $installedModulesCacheManager->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($this->expectedInstalledModules));
        $this->proxy->setInstalledModulesCacheManager($installedModulesCacheManager);
    }

    /** @test */
    public function canGetAllAvailableByEnvironment()
    {
        $modules = $this->proxy->getAllAvailable();

        $this->assertEquals($this->expectedAvailableModules, $modules);
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
