<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\OneTimeIdentity
 */
final class OneTimeIdentityTest extends TestCase
{

    public function test_Creating_One_Time_Identity()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':account'), $this->equalTo(3), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\OneTimeIdentity;
        $identity->setAccountId(3);

        $instance = new OneTimeIdentity($pdo, 'table');
        $instance->store($identity);
    }


    public function test_Updating_One_Time_Identity()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':id'), $this->equalTo(82), null],
                [$this->equalTo(':status'), $this->equalTo(9), null],
                [$this->equalTo(':used'), $this->greaterThanOrEqual(1), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\OneTimeIdentity;
        $identity->setId(82);
        $identity->setStatus(9);

        $instance = new OneTimeIdentity($pdo, 'table');
        $instance->store($identity);
    }
}
