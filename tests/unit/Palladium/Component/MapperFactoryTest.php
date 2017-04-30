<?php

namespace Palladium\Component;

use RuntimeException;
use PDO;
use PHPUnit\Framework\TestCase;


/**
 * @covers Palladium\Component\MapperFactory
 */
class MapperFactoryTest extends TestCase
{

    public function test_Creation_of_New_Mapper_Instance()
    {
        $instance = new MapperFactory(new PDO('sqlite::memory:'), 'alpha');
        $result = $instance->create(\Mock\Mapper::class);

        $this->assertInstanceOf(\Mock\Mapper::class, $result);
        $this->assertInstanceOf(PDO::class, $result->getConnection());
        $this->assertSame('alpha', $result->getTable());
    }


    public function test_Reuse_of_Mapper_with_Same_Classname()
    {
        $instance = new MapperFactory(new PDO('sqlite::memory:'), 'alpha');
        $result = $instance->create(\Mock\Mapper::class);

        $this->assertInstanceOf(\Mock\Mapper::class, $result);
        $this->assertSame($result, $instance->create(\Mock\Mapper::class));
    }


    public function test_Scenario_when_Class_does_not_Exist()
    {
        $this->expectException(RuntimeException::class);

        $instance = new MapperFactory(new PDO('sqlite::memory:'), 'alpha');
        $result = $instance->create(\Foo\Bar::class);
    }
}
