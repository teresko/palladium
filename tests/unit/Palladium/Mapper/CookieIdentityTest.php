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
    /**
     * @test
     */
    public function creating_Cookie_Identity()
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
        $pdo->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));


        $identity = new Entity\CookieIdentity;
        $identity->setParentId(6);

        $instance = new CookieIdentity($pdo, 'table');
        $instance->store($identity);
    }


    /**
     * @test
     */
    public function updating_Cookie_Identity()
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


    /**
     * @test
     */
    public function retrieving_Cookie_Identity_Details()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_COOKIE), null],
                [$this->equalTo(':status'), $this->equalTo(3), null],
                [$this->equalTo(':account'), $this->equalTo(8), null],
                [$this->equalTo(':identifier'), $this->equalTo('foobar'), null],
                [$this->equalTo(':fingerprint'), $this->equalTo('3c9c30d9f665e74d515c842960d4a451c83a0125fd3de7392d7b37231af10c72ea58aedfcdf89a5765bf902af93ecf06'), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue(['id' => 42]));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\CookieIdentity;
        $identity->setStatus(3);
        $identity->setAccountId(8);
        $identity->setSeries('foobar');

        $instance = new CookieIdentity($pdo, 'table');
        $instance->fetch($identity);
    }
}
