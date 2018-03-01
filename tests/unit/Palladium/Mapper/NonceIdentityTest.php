<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\NonceIdentity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class NonceIdentityTest extends TestCase
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


        $identity = new Entity\NonceIdentity;
        $identity->setAccountId(3);

        $instance = new NonceIdentity($pdo, 'table');
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


        $identity = new Entity\NonceIdentity;
        $identity->setId(82);
        $identity->setStatus(9);

        $instance = new NonceIdentity($pdo, 'table');
        $instance->store($identity);
    }


    /**
     * @test
     */
    public function retrieve_Nonce_Identity_by_Identifier_with_No_Data()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_NONCE), null],
                [$this->equalTo(':status'), $this->equalTo(Entity\Identity::STATUS_ACTIVE), null],
                [$this->equalTo(':fingerprint'), $this->equalTo('3c9c30d9f665e74d515c842960d4a451c83a0125fd3de7392d7b37231af10c72ea58aedfcdf89a5765bf902af93ecf06'), null],
                [$this->equalTo(':identifier'), $this->equalTo('foobar'), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue(null));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\NonceIdentity;
        $identity->setIdentifier('foobar');

        $instance = new NonceIdentity($pdo, 'table');
        $instance->fetch($identity);

        $this->assertNull($identity->getId());
    }


    /**
     * @test
     */
    public function retrieve_Nonce_Identity_by_Identifier()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_NONCE), null],
                [$this->equalTo(':status'), $this->equalTo(Entity\Identity::STATUS_ACTIVE), null],
                [$this->equalTo(':fingerprint'), $this->equalTo('3c9c30d9f665e74d515c842960d4a451c83a0125fd3de7392d7b37231af10c72ea58aedfcdf89a5765bf902af93ecf06'), null],
                [$this->equalTo(':identifier'), $this->equalTo('foobar'), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue(['id' => 4]));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\NonceIdentity;
        $identity->setIdentifier('foobar');

        $instance = new NonceIdentity($pdo, 'table');
        $instance->fetch($identity);

        $this->assertSame(4, $identity->getId());
    }
}
