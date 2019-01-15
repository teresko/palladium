<?php

namespace Palladium\Mapper;

use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;
use Palladium\Entity;

/**
 * @covers Palladium\Mapper\StandardIdentity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class StandardIdentityTest extends TestCase
{
    /**
     * @test
     */
    public function creating_Standard_Identity()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_STANDARD), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));
        $pdo->expects($this->once())->method('lastInsertId')->will($this->returnValue(1));


        $identity = new Entity\StandardIdentity;
        $identity->setAccountId(3);

        $instance = new StandardIdentity($pdo, 'table');
        $instance->store($identity);
    }


    /**
     * @test
     */
    public function updating_Standard_Identity()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':id'), $this->equalTo(43), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\StandardIdentity;
        $identity->setId(43);

        $instance = new StandardIdentity($pdo, 'table');
        $instance->store($identity);
    }


    /**
     * @test
     */
    public function updating_Standard_Identity_with_Token_Payload()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':id'), $this->equalTo(43), null]
            );

        $pdo = $this
                ->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\StandardIdentity;
        $identity->setId(43);
        $identity->setTokenPayload([
            'foo' => 'bar',
        ]);

        $instance = new StandardIdentity($pdo, 'table');
        $instance->store($identity);
    }



    /**
     * @test
     */
    public function checking_that_Entry_exists()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_STANDARD), null],
                [$this->equalTo(':fingerprint'), $this->equalTo('3c9c30d9f665e74d515c842960d4a451c83a0125fd3de7392d7b37231af10c72ea58aedfcdf89a5765bf902af93ecf06'), null],
                [$this->equalTo(':identifier'), $this->equalTo('foobar'), null],
                [$this->equalTo(':now'), $this->anything(), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue(['1' => 1]));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));


        $identity = new Entity\StandardIdentity;
        $identity->setIdentifier('foobar');

        $instance = new StandardIdentity($pdo, 'table');
        $this->assertTrue($instance->exists($identity));
    }


    /**
     * @test
     */
    public function load_Identity_Data_by_Id()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_STANDARD), null],
                [$this->equalTo(':id'), $this->equalTo(42), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue([
                'identifier' => 'foobar',
                'tokenPayload' => '[\'a\' => \'b\']',
            ]));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));

        $identity = new Entity\StandardIdentity;
        $identity->setId(42);

        $instance = new StandardIdentity($pdo, 'table');
        $instance->fetch($identity);
    }


    /**
     * @test
     */
    public function load_Identity_Data_by_Identifier()
    {
        $statement = $this
            ->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->method('bindValue')
            ->withConsecutive(
                [$this->equalTo(':type'), $this->equalTo(Entity\Identity::TYPE_STANDARD), null],
                [$this->equalTo(':identifier'), $this->equalTo('foobar'), null],
                [$this->equalTo(':fingerprint'), $this->equalTo('3c9c30d9f665e74d515c842960d4a451c83a0125fd3de7392d7b37231af10c72ea58aedfcdf89a5765bf902af93ecf06'), null]
            );
        $statement
            ->method('fetch')
            ->will($this->returnValue([
                'id' => 3,
                'tokenPayload' => '[]',
            ]));

        $pdo = $this
            ->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->getMock();
        $pdo->expects($this->once())->method('prepare')->will($this->returnValue($statement));

        $identity = new Entity\StandardIdentity;
        $identity->setIdentifier('foobar');

        $instance = new StandardIdentity($pdo, 'table');
        $instance->fetch($identity);
    }
}
