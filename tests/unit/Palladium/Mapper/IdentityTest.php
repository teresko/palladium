<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\Identity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class IdentityTest extends TestCase
{

    public function test_Storing_Identity()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':id'), $this->equalTo(3), null],
                [$this->equalTo(':used'), $this->equalTo(1493377286), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\Identity;
        $identity->setId(3);
        $identity->setLastUsed(1493377286);

        $instance = new Identity($pdo, 'table');
        $instance->store($identity);
    }


    public function test_Retrieving_Identity_by_Id()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':id'), $this->equalTo(32), null]
            );
        $statement
            ->expects($this->once())->method('fetch')
            ->will($this->returnValue(['parentId' => '8']));

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\Identity;
        $identity->setId(32);

        $instance = new Identity($pdo, 'table');
        $instance->fetch($identity);

        $this->assertSame(8, $identity->getParentId());
    }


    public function test_Retrieving_Identity_by_Token()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':token'), $this->equalTo('12345678901234567890123456789012'), null],
                [$this->equalTo(':action'), $this->equalTo(Entity\Identity::ACTION_VERIFY), null],
                [$this->equalTo(':expires'), $this->equalTo(1493377286), null]
            );
        $statement
            ->expects($this->once())->method('fetch')
            ->will($this->returnValue(['id' => '8', 'tokenPayload' => null]));

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\Identity;
        $identity->setToken('12345678901234567890123456789012');
        $identity->setTokenAction(Entity\Identity::ACTION_VERIFY);
        $identity->setTokenEndOfLife(1493377286);

        $instance = new Identity($pdo, 'table');
        $instance->fetch($identity);
    }
}
