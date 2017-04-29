<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;
use Mock\Factory;

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
        $basic = $this
                    ->getMockBuilder(Mapper\Identity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $basic->expects($this->any())->method('store');
        // $mapper->expects($this->once())->method('exists')->will($this->returnValue(false));

        $cookie = $this
                    ->getMockBuilder(Mapper\CookieIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cookie->expects($this->any())->method('store');
        $cookie->expects($this->once())->method('exists')->will($this->returnValue(false));

        $factory = new Factory([
            Mapper\Identity::class => $basic,
            Mapper\CookieIdentity::class => $cookie,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\EmailIdentity;
        $affected->setUserId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithPassword($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getUserId());
    }

}
