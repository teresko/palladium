<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;

use Palladium\Entity;
use Palladium\Mapper;

/**
 * @covers Palladium\Service\Identification
 */
final class IdentificationTest extends TestCase
{

    public function test_Logging_in_with_Password()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\Identity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->any())->method('store');

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $factory->method('create')->will($this->returnValue($mapper));

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\EmailIdentity;
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $this->assertInstanceOf(Entity\CookieIdentity::class, $instance->loginWithPassword($affected, 'alpha'));
    }

}
