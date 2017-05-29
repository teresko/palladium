<?php

namespace Palladium\Component;

use Exception;
use ReflectionClass;
use PHPUnit\Framework\TestCase;


/**
 * @covers Palladium\Component\Collection
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CollectionTest extends TestCase
{

    private function buildItem($itemId)
    {
        $item = $this
                    ->getMockBuilder(\Mock\Entity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $item->method('getId')->willReturn($itemId);
        return $item;
    }


    public function test_ArrayAccess_Retrieval()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(3);

        $this->assertNull($instance[0]);
        $instance->addEntity($item);
        $this->assertNotNull($instance[0]);
        // $this->assertSame($item, $instance[0]);
    }

    public function test_ArrayAccess_Retrieval_After_Unspecified_Addition()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(13);

        $instance[] = $item;
        $this->assertSame($item, $instance[0]);
    }


    public function test_ArrayAccess_Retrieval_After_Specified_Addition()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(2);

        $instance[1] = $item;
        $this->assertEquals($instance[1], $item);
    }

    public function test_ArrayAccess_Unset()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(2);

        $instance[] = $item;
        $this->assertEquals($instance[0], $item);
        unset($instance[0]);
        $this->assertNull($instance[0]);
    }


    public function test_ArrayAccess_Exists()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(9);

        $instance[] = $item;
        $this->assertEquals($instance[0], $item);
        $this->assertTrue(isset($instance[0]));
        $this->assertFalse(isset($instance[3]));
    }


    public function test_Pagination_Offset()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);

        $instance->setOffset(2);
        $this->assertSame(2, $instance->getOffset());

        $instance->setOffset(-9);
        $this->assertSame(0, $instance->getOffset());

        $instance->setOffset('124a');
        $this->assertSame(124, $instance->getOffset());
    }


    public function test_Pagination_Limit()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);

        $instance->setLimit(88);
        $this->assertSame(88, $instance->getLimit());

        $instance->setLimit(-12);
        $this->assertSame(0, $instance->getLimit());

        $instance->setLimit('11II');
        $this->assertSame(11, $instance->getLimit());
    }


    public function test_Pagination_Total()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);

        $instance->setTotal(1);
        $this->assertSame(1, $instance->getTotal());

        $instance->setTotal(-2);
        $this->assertSame(0, $instance->getTotal());

        $instance->setTotal('51x2');
        $this->assertSame(51, $instance->getTotal());
    }


    public function test_Foreach_Behavior_for_Collection()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $item = $this->buildItem(9);

        $instance->addEntity($item);
        $counter = 0;

        foreach ($instance as $key => $value) {
            $counter += 1;
            $this->assertSame(0, $key);
            $this->assertSame($item, $value);
        }

        $this->assertSame(1, $counter);
    }


    public function test_Retrieval_of_Id_List()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $instance[] = $this->buildItem(9);
        $instance[] = $this->buildItem(2);

        $this->assertSame([2, 9], $instance->getIds());

        $instance[1] = $this->buildItem(14);
        $this->assertSame([9, 14], $instance->getIds());
    }

    public function test_Countable_Interface_Compatibility()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $this->assertSame(0, count($instance));

        $instance[] = $this->buildItem(9);
        $instance[] = $this->buildItem(2);

        $this->assertSame(2, count($instance));

        unset($instance[1]);

        $this->assertSame(1, count($instance));
    }


    public function test_Complicate_Usecase_of_Unset_Operation()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $instance[] = $this->buildItem(5);
        $instance[] = $this->buildItem(2);
        $instance[] = $this->buildItem(8);
        $instance[] = $this->buildItem(9);

        unset($instance[1]);
        unset($instance[2]);

        $this->assertSame([5, 9], $instance->getIds());
    }


    public function test_Purge_of_Data()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $instance[] = $this->buildItem(5);
        $instance[] = $this->buildItem(2);

        $this->assertSame(2, count($instance));
        $instance->purge();
        $this->assertSame(0, count($instance));

        $instance[] = $this->buildItem(8);
        $this->assertSame([8], $instance->getIds());
    }


    public function test_Replacement_of_Current_Collection_with_a_Different_One()
    {
        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity');

        $instance[] = $this->buildItem(5);
        $instance[] = $this->buildItem(2);

        $collection = $this->getMockForAbstractClass(Collection::class);
        $collection->method('buildEntity');

        $collection[] = $this->buildItem(12);
        $collection[] = $this->buildItem(21);
        $collection[] = $this->buildItem(4);
        $collection[] = $this->buildItem(9);

        $instance->replaceWith($collection);

        $this->assertSame([4, 9, 12, 21], $instance->getIds());
    }


    public function test_Addition_of_Entity()
    {
        $entity = $this
                    ->getMockBuilder(\Mock\Entity::class)
                    ->setMethods(['setId', 'setAlpha', 'setBetaGamma'])
                    ->disableOriginalConstructor()
                    ->getMock();
        $entity->expects($this->once())->method('setAlpha')->with($this->equalTo(12));
        $entity->expects($this->once())->method('setBetaGamma')->with($this->equalTo('test'));


        $instance = $this->getMockForAbstractClass(Collection::class);
        $instance->method('buildEntity')->will($this->returnValue($entity));

        $instance->addBlueprint([
            'alpha' => 12,
            'beta_gamma' => 'test',
        ]);
    }
}
