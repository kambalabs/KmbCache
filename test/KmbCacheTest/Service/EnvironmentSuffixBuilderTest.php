<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\DefaultSuffixBuilder;
use KmbDomain\Model\Environment;

class EnvironmentSuffixBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canBuild()
    {
        $environment = new Environment('PF1');
        $environment->setParent(new Environment('STABLE'));
        $suffixBuilder = new DefaultSuffixBuilder();

        $suffix = $suffixBuilder->build($environment);

        $this->assertEquals('.STABLE_PF1', $suffix);
    }

    /** @test */
    public function canBuildWithNoEnvironment()
    {
        $suffixBuilder = new DefaultSuffixBuilder();

        $suffix = $suffixBuilder->build(null);

        $this->assertEmpty($suffix);
    }
}
