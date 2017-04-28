<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;
use Palladium\Contract\CanPersistIdentity;

use Palladium\Entity\EmailIdentity;
use Palladium\Entity\Identity;
use Palladium\Exception\IdentityNotVerified;

/**
 * @covers Palladium\Service\Recovery
 */
final class RecoveryTest extends TestCase
{

    public function test_Initialization_of_Password_Reset_Process()
    {
        $mapper = $this->getMockBuilder(CanPersistIdentity::class)->getMock();
        $mapper->expects($this->once())->method('store')->will($this->returnValue(true));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();


        $affected = new EmailIdentity;

        $instance = new Recovery($factory, $logger);
        $instance->markForReset($affected);

        $this->assertNotNull($affected->getToken());
        $this->assertSame(Identity::ACTION_RESET, $affected->getTokenAction());
    }


    public function test_Passing_of_Identity_that_cannot_be_Reset()
    {
        $this->expectException(IdentityNotVerified::class);

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('warning');


        $affected = new EmailIdentity;
        $affected->setStatus(Identity::STATUS_NEW);

        $instance = new Recovery($factory, $logger);
        $instance->markForReset($affected);
    }


    public function test_Completion_of_Password_Reset()
    {
        $mapper = $this->getMockBuilder(CanPersistIdentity::class)->getMock();
        $mapper->expects($this->once())->method('store')->will($this->returnValue(true));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new EmailIdentity;
        $affected->setToken('12345678901234567890123456789012');

        $instance = new Recovery($factory, $logger);
        $instance->resetIdentityPassword($affected, 'password');

        $this->assertNull($affected->getToken());
    }
}
