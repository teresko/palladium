<?php

namespace Palladium\Repository;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Repository\Identity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class IdentityTest extends TestCase
{
    /**
     * @test
     */
    public function use_assigned_mapper_for_loading_entity()
    {
        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper,
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $instance->load($item);

        $this->assertSame('loaded', $item->getAction());
    }


    /**
     * @test
     */
    public function use_assigned_mapper_for_storing_entity()
    {
        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper,
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $instance->save($item);

        $this->assertSame('saved', $item->getAction());
    }


    /**
     * @test
     */
    public function use_assigned_mapper_for_deleting_entity()
    {
        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper,
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $instance->delete($item);

        $this->assertSame('deleted', $item->getAction());
    }


    /**
     * @test
     */
    public function use_assigned_mapper_for_checking_whether_entity_exists()
    {
        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper(true),
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $this->assertSame(true, $instance->has($item));
    }


    /**
     * @test
     */
    public function deny_definition_of_fake_entity_in_repo()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([]));
        $instance->define('FooBar', \Mock\RepoMapper::class);
    }


    /**
     * @test
     */
    public function deny_definition_of_fake_mapper_in_repo()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([]));
        $instance->define(\Mock\RepoEntity::class, 'FooBar');
    }


    /**
     * @test
     */
    public function deny_override_with_undefined_mapper()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper,
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $instance->load($item, 'FooBar');
    }
}
