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
    public function use_Assigned_by_ClassName_Mapper_for_Loading_Entity()
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
    public function use_Assigned_by_ClassName_Mapper_for_Storing_Entity()
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
    public function use_Assigned_by_ClassName_Mapper_for_Deleting_Entity()
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
    public function use_Assigned_by_ClassName_Mapper_for_Checking_whether_Entity_Exists()
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
    public function deny_Definition_of_Fake_Entity_in_Repo()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([]));
        $instance->define('FooBar', \Mock\RepoMapper::class);
    }


    /**
     * @test
     */
    public function deny_Definition_of_Fake_Mapper_in_Repo()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([]));
        $instance->define(\Mock\RepoEntity::class, 'FooBar');
    }


    /**
     * @test
     */
    public function deny_Override_with_Undefined_Mapper()
    {
        $this->expectException(\RuntimeException::class);

        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper,
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $instance->load($item, 'FooBar');
    }



    /**
     * @test
     */
    public function use_Assigned_as_Instance_Mapper_for_Loading_Entity()
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
    public function use_Assigned_as_Instance_Mapper_for_Storing_Entity()
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
    public function use_Assigned_as_Instance_Mapper_for_Deleting_Entity()
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
    public function use_Assigned_as_Instance_Mapper_for_Checking_whether_Entity_Exists()
    {
        $instance = new Identity(new \Mock\Factory([
            \Mock\RepoMapper::class => new \Mock\RepoMapper(true),
        ]));
        $instance->define(\Mock\RepoEntity::class, \Mock\RepoMapper::class);

        $item = new \Mock\RepoEntity;
        $this->assertSame(true, $instance->has($item));
    }

}
