<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\NodeStatisticsDataContextBuilder;
use KmbCacheTest\Bootstrap;

class NodeStatisticsDataContextBuilderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canCreateService()
    {
        /** @var NodeStatisticsDataContextBuilder $service */
        $service = Bootstrap::getServiceManager()->get('KmbCache\Service\NodeStatisticsDataContextBuilder');

        $this->assertInstanceOf('KmbCache\Service\NodeStatisticsDataContextBuilder', $service);
        $this->assertInstanceOf('KmbPuppetDb\Query\NodesV3EnvironmentsQueryBuilder', $service->getNodesEnvironmentsQueryBuilder());
        $this->assertInstanceOf('KmbPermission\Service\EnvironmentInterface', $service->getPermissionEnvironmentService());
    }
}
