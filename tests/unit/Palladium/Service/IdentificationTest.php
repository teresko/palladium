<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;
use Mock\Factory;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Exception\DenialOfServiceAttempt;
use Palladium\Exception\IdentityExpired;

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


    public function test_Failed_Attemt_to_Login_with_Missing_Identity()
    {
        $this->expectException(DenialOfServiceAttempt::class);

        $factory = new Factory;
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithCookie($affected, 'alpha');
    }


    public function test_Failed_Attemt_to_Login_with_Expired_Identity()
    {
        $this->expectException(IdentityExpired::class);

        $cookie = $this
                    ->getMockBuilder(Mapper\CookieIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cookie->expects($this->once())->method('store');

        $factory = new Factory([
            Mapper\CookieIdentity::class => $cookie,
        ]);
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $affected->setId(432);
        $affected->setExpiresOn(time() - 10000);

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithCookie($affected, 'alpha');
    }


    public function test_Logging_in_with_Cookie()
    {
        $cookie = $this
                    ->getMockBuilder(Mapper\CookieIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $cookie->expects($this->once())->method('store');

        $factory = new Factory([
            Mapper\CookieIdentity::class => $cookie,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $affected->setId(7);
        $affected->setUserId(3);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');
        $affected->setExpiresOn(time() + 10000);

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithCookie($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getUserId());
    }

}
