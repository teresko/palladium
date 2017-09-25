<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Repository\Identity as Repository;

use Palladium\Exception\IdentityConflict;
use Palladium\Exception\AccountNotFound;
use Palladium\Exception\TokenNotFound;
use Palladium\Entity;
use Palladium\Mapper;

/**
 * @covers Palladium\Service\Registration
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class RegistrationTest extends TestCase
{

    public function test_Failure_of_Creating_Duplicate_Email_Identity()
    {
        $this->expectException(IdentityConflict::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('has')->will($this->returnValue(true));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');


        $instance = new Registration($repository, $logger);
        $instance->createEmailIdentity('foo@example.com', 'password');
    }


    public function test_Creation_of_Email_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('has')->will($this->returnValue(false));
        $repository->expects($this->once())->method('save');

        $instance = new Registration(
            $repository,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(Entity\Identity::class, $instance->createEmailIdentity('foo@example.com', 'password'));
    }


    public function test_Binding_of_Account()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');


        $instance = new Registration($repository, $logger);
        $affected = new Entity\Identity;
        $instance->bindAccountToIdentity(42, $affected);
        $this->assertSame(42, $affected->getAccountId());
    }


    public function test_Verification_of_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');


        $instance = new Registration($repository, $logger);
        $affected = new Entity\EmailIdentity;
        $affected->setId(2);
        $affected->setStatus(Entity\Identity::STATUS_NEW);

        $instance->verifyEmailIdentity($affected);
        $this->assertSame(Entity\Identity::STATUS_ACTIVE, $affected->getStatus());
    }


    public function test_Creation_of_OneTime_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $instance = new Registration(
            $repository,
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );

        $this->assertInstanceOf(Entity\Identity::class, $instance->createNonceIdentity(3));
    }
}
