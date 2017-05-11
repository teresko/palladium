<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Exception\IdentityNotVerified;

/**
 * @covers Palladium\Service\Recovery
 */
final class RecoveryTest extends TestCase
{

    public function test_Initialization_of_Password_Reset_Process()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();


        $affected = new Entity\EmailIdentity;

        $instance = new Recovery($factory, $logger);
        $instance->markForReset($affected);

        $this->assertNotNull($affected->getToken());
        $this->assertSame(Entity\Identity::ACTION_RESET, $affected->getTokenAction());
    }


    public function test_Passing_of_Identity_that_cannot_be_Reset()
    {
        $this->expectException(IdentityNotVerified::class);

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');


        $affected = new Entity\EmailIdentity;
        $affected->setStatus(Entity\Identity::STATUS_NEW);

        $instance = new Recovery($factory, $logger);
        $instance->markForReset($affected);
    }


    public function test_Completion_of_Password_Reset()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store')->will($this->returnValue(true));

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\EmailIdentity;
        $affected->setToken('12345678901234567890123456789012');

        $instance = new Recovery($factory, $logger);
        $instance->resetIdentityPassword($affected, 'password');

        $this->assertNull($affected->getToken());
    }
}
