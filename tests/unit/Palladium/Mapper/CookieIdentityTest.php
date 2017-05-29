<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\CookieIdentity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class CookieIdentityTest extends TestCase
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
                [$this->equalTo(':parent'), $this->equalTo(6), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\CookieIdentity;
        $identity->setParentId(6);

        $instance = new CookieIdentity($pdo, 'table');
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
                [$this->equalTo(':id'), $this->equalTo(134), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\CookieIdentity;
        $identity->setId(134);

        $instance = new CookieIdentity($pdo, 'table');
        $instance->store($identity);
    }

}
