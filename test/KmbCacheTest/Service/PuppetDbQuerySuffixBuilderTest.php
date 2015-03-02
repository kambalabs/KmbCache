<?php
namespace KmbCacheTest\Service;

use KmbCache\Service\PuppetDbQuerySuffixBuilder;
use KmbPuppetDb\Query\Query;

class PuppetDbQuerySuffixBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
    public function canBuildQuery()
    {
        $query = new Query(['=', 'environment', 'STABLE_PF1']);
        $querySuffixBuilder = new PuppetDbQuerySuffixBuilder();

        $suffix = $querySuffixBuilder->build($query);

        $this->assertEquals('_2a62ff13810414a40b8741c8333bdb54c3d473c9', $suffix);
    }

    /** @test */
    public function canBuildNullQuery()
    {
        $querySuffixBuilder = new PuppetDbQuerySuffixBuilder();

        $this->assertEquals('', $querySuffixBuilder->build(null));
    }

    /** @test */
    public function canBuildArrayQuery()
    {
        $querySuffixBuilder = new PuppetDbQuerySuffixBuilder();

        $suffix = $querySuffixBuilder->build(['=', 'environment', 'STABLE_PF1']);

        $this->assertEquals('_2a62ff13810414a40b8741c8333bdb54c3d473c9', $suffix);
    }
}
