<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Exception\IdentityNotFound;

/**
 * @covers Palladium\Service\Search
 */
final class SearchTest extends TestCase
{

    public function test_Looking_for_Identity_by_Id()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\Identity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function(HasId $entity) {
                $entity->setAccountId(1);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(
            Entity\Identity::class,
            $instance->findIdentityById(12121)
        );
    }



    public function test_Failure_to_Find_Identity_by_Id()
    {
        $this->expectException(IdentityNotFound::class);

        $mapper = $this
                    ->getMockBuilder(Mapper\Identity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $instance->findIdentityById(42);
    }


    public function test_Looking_for_Email_Identity_by_Identifier()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function(HasId $entity) {
                $entity->setId(1);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(
            Entity\EmailIdentity::class,
            $instance->findEmailIdentityByIdentifier('foo@example.com')
        );
    }


    public function test_Failure_to_Find_Email_Identity_by_Identifier()
    {
        $this->expectException(IdentityNotFound::class);

        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $instance->findEmailIdentityByIdentifier('foo@example.com');
    }


    public function test_Looking_for_Email_Identity_by_Token()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function(HasId $entity) {
                $entity->setId(1);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(
            Entity\EmailIdentity::class,
            $instance->findEmailIdentityByToken('12345678901234567890123456789012', Entity\Identity::ACTION_ANY)
        );
    }


    public function test_Failure_to_Find_Email_Identity_by_Token()
    {
        $this->expectException(IdentityNotFound::class);

        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(
            Entity\EmailIdentity::class,
            $instance->findEmailIdentityByToken('12345678901234567890123456789012', Entity\Identity::ACTION_ANY)
        );
    }


    public function test_Looking_for_Cookie_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\CookieIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function(HasId $entity) {
                $entity->setId(1);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(
            Entity\CookieIdentity::class,
            $instance->findCookieIdentity(123, '12345678901234567890123456789012')
        );
    }


    public function test_Looking_for_Identity_with_Given_Account_Id()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\IdentityCollection::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($collection) {
                $collection->addBlueprint(['id' => 1]);
                $collection->addBlueprint(['id' => 2]);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $list = $instance->findIdentitiesByAccountId(4);
        $this->assertSame([1, 2], $list->getIds());
    }


    public function test_Looking_for_Identity_with_Given_Parent_Id()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\IdentityCollection::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($collection) {
                $collection->addBlueprint(['id' => 7]);
                $collection->addBlueprint(['id' => 3]);
            }));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Search(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $list = $instance->findIdentitiesByParentId(123);
        $this->assertSame([3, 7], $list->getIds());
    }
}
