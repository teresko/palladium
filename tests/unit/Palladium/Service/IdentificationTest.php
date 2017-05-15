<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;
use Mock\Factory;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Exception\IdentityExpired;
use Palladium\Exception\CompromisedCookie;
use Palladium\Exception\PasswordMismatch;

/**
 * @covers Palladium\Service\Identification
 */
final class IdentificationTest extends TestCase
{

    public function test_Logging_in_with_Password()
    {
        $this->expectException(PasswordMismatch::class);

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\EmailIdentity;
        $affected->setAccountId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $instance->loginWithPassword($affected, 'beta');
    }


    public function test_Failure_to_Login_with_Password()
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

        $factory = new Factory([
            Mapper\Identity::class => $basic,
            Mapper\CookieIdentity::class => $cookie,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\EmailIdentity;
        $affected->setAccountId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithPassword($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getAccountId());
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
        $affected->setAccountId(3);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');
        $affected->setExpiresOn(time() + 10000);

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithCookie($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getAccountId());
    }


    public function test_Logging_in_with_Cookie_Failure()
    {
        $this->expectException(CompromisedCookie::class);

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
        $affected->setAccountId(3);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');
        $affected->setExpiresOn(time() + 10000);

        $instance = new Identification($factory, $logger);
        $result = $instance->loginWithCookie($affected, 'beta');
    }


    public function test_Logout_of_Identity()
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
        $logger->expects($this->once())->method('info');

        $affected = new Entity\CookieIdentity;
        $affected->setId(99);
        $affected->setExpiresOn(time() + 10000);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');

        $instance = new Identification($factory, $logger);
        $result = $instance->logout($affected, 'alpha');
    }


    public function test_Discardint_of_the_Related_Cookies()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\IdentityCollection::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = new Factory([
            Mapper\IdentityCollection::class => $mapper,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $entity = new Entity\Identity;
        $this->assertNull($entity->getStatus());

        $list = new Entity\IdentityCollection;
        $list->addEntity($entity);

        $instance = new Identification($factory, $logger);
        $instance->discardIdentityCollection($list);

        $this->assertNotNull($entity->getStatus());
    }


    public function test_Changing_of_Password_for_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\EmailIdentity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = new Factory([
            Mapper\EmailIdentity::class => $mapper,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');

        $affected = new Entity\EmailIdentity;
        $affected->setId(99);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $instance->changePassword($affected, 'alpha', 'password');

        $this->assertTrue($affected->matchPassword('password'));
    }


    public function test_Failure_to_Change_of_Password_for_Identity()
    {
        $this->expectException(PasswordMismatch::class);

        $factory = $this->getMockBuilder(CanCreateMapper::class)->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');

        $affected = new Entity\EmailIdentity;
        $affected->setId(99);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($factory, $logger);
        $instance->changePassword($affected, 'wrong', 'password');
    }


    public function test_Blocking_of_Identity()
    {
        $mapper = $this
                    ->getMockBuilder(Mapper\Identity::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $mapper->expects($this->once())->method('store');

        $factory = new Factory([
            Mapper\Identity::class => $mapper,
        ]);

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $instance = new Identification($factory, $logger);
        $instance->blockIdentity(new Entity\Identity);
    }
}
