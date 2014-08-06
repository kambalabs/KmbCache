<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\QuerySuffixBuilder;
use KmbPuppetDb\Query\Query;

class QuerySuffixBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canBuildQuery()
    {
        $query = new Query(['=', 'environment', 'STABLE_PF1']);
        $querySuffixBuilder = new QuerySuffixBuilder();

        $suffix = $querySuffixBuilder->build($query);

        $this->assertEquals('_2a62ff13810414a40b8741c8333bdb54c3d473c9', $suffix);
    }

    /** @test */
    public function canBuildNullQuery()
    {
        $querySuffixBuilder = new QuerySuffixBuilder();

        $this->assertEquals('', $querySuffixBuilder->build(null));
    }

    /** @test */
    public function canBuildArrayQuery()
    {
        $querySuffixBuilder = new QuerySuffixBuilder();

        $suffix = $querySuffixBuilder->build(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals('_2a62ff13810414a40b8741c8333bdb54c3d473c9', $suffix);
    }
}
