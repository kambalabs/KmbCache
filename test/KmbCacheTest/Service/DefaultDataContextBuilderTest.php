<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\DefaultDataContextBuilder;
use KmbDomain\Model\Environment;

class DefaultDataContextBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canBuild()
    {
        $environment = new Environment('STABLE');
        $builder = new DefaultDataContextBuilder();

        $this->assertEquals($environment, $builder->build($environment));
    }
}
