<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;

use Palladium\Exception\IdentityConflict;
use Palladium\Exception\AccountNotFound;
use Palladium\Exception\TokenNotFound;
use Palladium\Entity;
use Palladium\Mapper;

/**
 * @covers Palladium\Service\Registration
 */
final class RegistrationTest extends TestCase
{

    public function test_Failure_of_Creating_Duplicate_Email_Identity()
    {
        $this->expectException(IdentityConflict::class);

        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('exists')->will($this->returnValue(true));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');


        $instance = new Registration($factory, $logger);
        $instance->createEmailIdentity('foo@example.com', 'password');
    }


    public function test_Creation_of_Email_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('exists')->will($this->returnValue(false));
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Registration(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(Entity\Identity::class, $instance->createEmailIdentity('foo@example.com', 'password'));
    }


    public function test_Binding_of_Account()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\IdentityAccount::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');


        $instance = new Registration($factory, $logger);
        $affected = new Entity\Identity;
        $instance->bindAccountToIdentity(42, $affected);
        $this->assertSame(42, $affected->getAccountId());
    }


    public function test_Verification_of_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\IdentityAccount::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');


        $instance = new Registration($factory, $logger);
        $affected = new Entity\EmailIdentity;
        $affected->setId(2);
        $affected->setStatus(Entity\Identity::STATUS_NEW);

        $instance->verifyEmailIdentity($affected);
        $this->assertSame(Entity\Identity::STATUS_ACTIVE, $affected->getStatus());
    }


    public function test_Creation_of_OneTime_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\NonceIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));


        $instance = new Registration(
            $factory,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(Entity\Identity::class, $instance->createNonceIdentity(3));
    }
}
