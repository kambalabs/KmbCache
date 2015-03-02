<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\NodeStatisticsDataContextBuilder;
use KmbDomain\Model\Environment;

class NodeStatisticsDataContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canBuild()
    {
        $environment = new Environment('STABLE');
        $child = new Environment('PF1');
        $environment->addChild($child);
        $permissionService = $this->getMock('KmbPermission\Service\EnvironmentInterface');
        $permissionService->expects($this->any())
            ->method('getAllReadable')
            ->will($this->returnValue([$environment, $child]));
        $queryBuilder = $this->getMock('KmbPuppetDb\Query\QueryBuilderInterface');
        $queryBuilder->expects($this->any())
            ->method('build')
            ->with([$environment, $child])
            ->will($this->returnValue(['=', 'environment', 'STABLE']));
        $contextBuilder = new NodeStatisticsDataContextBuilder();
        $contextBuilder->setPermissionEnvironmentService($permissionService);
        $contextBuilder->setNodesEnvironmentsQueryBuilder($queryBuilder);

        $context = $contextBuilder->build($environment);

        $this->assertEquals(['=', 'environment', 'STABLE'], $context);
    }
}
