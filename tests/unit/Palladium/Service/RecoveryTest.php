<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;
use Palladium\Contract\HasId;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Repository\Identity as Repository;
use Palladium\Exception\IdentityNotVerified;

/**
 * @covers Palladium\Service\Recovery
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class RecoveryTest extends TestCase
{

    public function test_Initialization_of_Password_Reset_Process()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();


        $affected = new Entity\StandardIdentity;

        $instance = new Recovery($repository, $logger);
        $instance->markForReset($affected);

        $this->assertNotNull($affected->getToken());
        $this->assertSame(Entity\Identity::ACTION_RESET, $affected->getTokenAction());
    }


    public function test_Passing_of_Identity_that_cannot_be_Reset()
    {
        $this->expectException(IdentityNotVerified::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');


        $affected = new Entity\StandardIdentity;
        $affected->setStatus(Entity\Identity::STATUS_NEW);

        $instance = new Recovery($repository, $logger);
        $instance->markForReset($affected);
    }


    public function test_Completion_of_Password_Reset()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\StandardIdentity;
        $affected->setToken('12345678901234567890123456789012');

        $instance = new Recovery($repository, $logger);
        $instance->resetIdentityPassword($affected, 'password');

        $this->assertNull($affected->getToken());
    }
}
