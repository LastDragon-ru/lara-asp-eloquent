<?php declare(strict_types = 1);

namespace LastDragon_ru\LaraASP\Eloquent\Mixins;

use Illuminate\Database\Query\Builder;
use LastDragon_ru\LaraASP\Testing\Package\TestCase;
use Traversable;

/**
 * @internal
 * @coversDefaultClass \LastDragon_ru\LaraASP\Eloquent\Mixins\QueryBuilderMixin
 */
class QueryBuilderMixinTest extends TestCase {
    /**
     * @covers ::getDefaultKeyName
     */
    public function testGetDefaultKeyNameQueryBuilder() {
        $this->assertTrue(Builder::hasMacro('getDefaultKeyName'));
        $this->assertEquals('id', $this->app->make('db')->query()->getDefaultKeyName());
    }

    /**
     * @covers ::iterator
     */
    public function testIteratorQueryBuilder() {
        $this->assertTrue(Builder::hasMacro('iterator'));
        $this->assertInstanceOf(Traversable::class, $this->app->make('db')->query()->iterator());
    }
}