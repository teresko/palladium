<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\IdentityCollection
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class IdentityCollectionTest extends TestCase
{

    public function test_Retrieving_Data_from_Storage_By_Parent_Id()
    {
        $collection = new Entity\IdentityCollection;
        $collection->forParentId(2);

        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement->expects($this->once())->method('execute');
        $statement
            ->expects($this->once())->method('fetchAll')
            ->will($this->returnValue([
                ['id' => '12'],
                ['id' => '15'],
            ]));


        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));

        $instance = new IdentityCollection($pdo, 'table');

        $instance->fetch($collection);
        $this->assertSame([12, 15], $collection->getIds());
    }


    public function test_Retrieving_Data_from_Storage_By_Account_Id()
    {
        $collection = new Entity\IdentityCollection;
        $collection->forAccountId(9);

        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement->expects($this->once())->method('execute');
        $statement
            ->expects($this->once())->method('fetchAll')
            ->will($this->returnValue([
                ['id' => '8'],
                ['id' => '31'],
                ['id' => '75'],
            ]));


        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));

        $instance = new IdentityCollection($pdo, 'table');

        $instance->fetch($collection);
        $this->assertSame([8, 31, 75], $collection->getIds());
    }


    public function test_Updating_Status()
    {
        $collection = new Entity\IdentityCollection;
        $collection->addBlueprint([
            'id' => 2,
            'status' => Entity\Identity::STATUS_EXPIRED,
        ]);
        $collection->addBlueprint([
            'id' => 8,
            'status' => Entity\Identity::STATUS_EXPIRED,
        ]);

        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('execute')
            ->withConsecutive(
                [$this->equalTo([
                   ':id' => 2,
                   ':status' => Entity\Identity::STATUS_EXPIRED,
                ])],
                [$this->equalTo([
                   ':id' => 8,
                   ':status' => Entity\Identity::STATUS_EXPIRED,
                ])]
            );


        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));

        $instance = new IdentityCollection($pdo, 'table');

        $instance->store($collection);
    }
}
