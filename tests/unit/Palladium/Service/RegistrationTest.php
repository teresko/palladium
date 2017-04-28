<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;
use Palladium\Contract\CanPersistIdentity;

use Palladium\Exception\IdentityDuplicated;
use Palladium\Exception\UserNotFound;
use Palladium\Entity\Identity;

/**
 * @covers Palladium\Service\Registration
 */
final class RegistrationTest extends TestCase
{

    public function test_Failure_of_Creating_Duplicate_Email_Identity()
    {
        $this->expectException(IdentityDuplicated::class);

        $mapper = $this->getMockBuilder(CanPersistIdentity::class)->getMock();
        $mapper->expects($this->once())->method('exists')->will($this->returnValue(true));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('warning');


        $instance = new Registration($factory, $logger);
        $instance->createEmailIdentity('foo@example.com', 'password');
    }


    public function test_Creation_of_Email_Identity()
    {
        $mapper = $this->getMockBuilder(CanPersistIdentity::class)->getMock();
        $mapper->expects($this->once())->method('exists')->will($this->returnValue(false));
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Registration(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(Identity::class, $instance->createEmailIdentity('foo@example.com', 'password'));
    }


    public function test_Failutre_of_User_Binding()
    {
        $this->expectException(UserNotFound::class);

        $instance = new Registration(
            $this->getMockBuilder(CanCreateMapper::class)->getMock(),
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $instance->bindIdentityToUser(new Identity, new \Mock\User(null));
    }


    public function test_Binding_of_User()
    {
        $mapper = $this->getMockBuilder(CanPersistIdentity::class)->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');


        $instance = new Registration($factory, $logger);
        $affected = new Identity;
        $instance->bindIdentityToUser($affected,  new \Mock\User(42));
        $this->assertSame(42, $affected->getUserId());
    }

}
