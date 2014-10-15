<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\CacheManager;
use KmbCache\Service\CacheManagerFactory;
use KmbCacheTest\Bootstrap;

class CacheManagerFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        $factory = new CacheManagerFactory();

        /** @var CacheManager $cacheManager */
        $cacheManager = $factory->createService(Bootstrap::getServiceManager());

        $this->assertInstanceOf('KmbCache\Service\CacheManager', $cacheManager);
        $this->assertInstanceOf('Zend\Cache\Storage\Adapter\Memory', $cacheManager->getCacheStorage());
        $this->assertInstanceOf('KmbPuppetDb\Service\NodeStatistics', $cacheManager->getNodeStatisticsService());
        $this->assertInstanceOf('KmbBase\DateTimeFactory', $cacheManager->getDateTimeFactory());
        $this->assertInstanceOf('KmbCache\Service\QuerySuffixBuilder', $cacheManager->getQuerySuffixBuilder());
        $this->assertInstanceOf('KmbPuppetDb\Query\QueryBuilderInterface', $cacheManager->getNodesEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPermission\Service\Environment', $cacheManager->getPermissionEnvironmentService());
        $this->assertInstanceOf('KmbPmProxy\Service\PuppetModuleInterface', $cacheManager->getPmProxyPuppetModuleService());
        $this->assertInstanceOf('Zend\Log\Logger', $cacheManager->getLogger());
    }
}
