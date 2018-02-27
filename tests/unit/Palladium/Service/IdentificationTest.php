<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Psr\Log\LoggerInterface;
use Palladium\Contract\CanCreateMapper;

use Palladium\Entity;
use Palladium\Mapper;
use Palladium\Repository\Identity as Repository;
use Palladium\Exception\IdentityExpired;
use Palladium\Exception\CompromisedCookie;
use Palladium\Exception\PasswordMismatch;
use Palladium\Exception\KeyMismatch;

/**
 * @covers Palladium\Service\Identification
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class IdentificationTest extends TestCase
{

    public function test_Failure_to_Login_with_Password()
    {
        $this->expectException(PasswordMismatch::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\StandardIdentity;
        $affected->setAccountId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($repository, $logger);
        $instance->loginWithPassword($affected, 'beta');
    }


    public function test_Logging_in_with_Password_where_Rehash_is_Triggered()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->any())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\StandardIdentity;
        $affected->setAccountId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN, 10);
        $instance->loginWithPassword($affected, 'alpha');

        $this->assertStringStartsWith('$2y$10', $affected->getHash());

    }


    public function test_Logging_in_with_Password()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->any())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\StandardIdentity;
        $affected->setAccountId(3);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($repository, $logger);
        $result = $instance->loginWithPassword($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getAccountId());
    }


    public function test_Failed_Attemt_to_Login_with_Expired_Identity()
    {
        $this->expectException(IdentityExpired::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $affected->setId(432);
        $affected->setExpiresOn(time() - 10000);

        $instance = new Identification($repository, $logger);
        $instance->loginWithCookie($affected, 'alpha');
    }


    public function test_Logging_in_with_Cookie()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $affected->setId(7);
        $affected->setAccountId(3);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');
        $affected->setExpiresOn(time() + 10000);

        $instance = new Identification($repository, $logger);
        $result = $instance->loginWithCookie($affected, 'alpha');
        $this->assertInstanceOf(Entity\CookieIdentity::class, $result);
        $this->assertSame(3, $result->getAccountId());
    }


    public function test_Logging_in_with_Cookie_Failure()
    {
        $this->expectException(CompromisedCookie::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $affected = new Entity\CookieIdentity;
        $affected->setId(7);
        $affected->setAccountId(3);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');
        $affected->setExpiresOn(time() + 10000);

        $instance = new Identification($repository, $logger);
        $instance->loginWithCookie($affected, 'beta');
    }


    public function test_Logout_of_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');

        $affected = new Entity\CookieIdentity;
        $affected->setId(99);
        $affected->setExpiresOn(time() + 10000);
        $affected->setHash('9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af');

        $instance = new Identification($repository, $logger);
        $instance->logout($affected, 'alpha');
    }


    public function test_Discardint_of_the_Related_Cookies()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $entity = new Entity\Identity;
        $this->assertNull($entity->getStatus());

        $list = new Entity\IdentityCollection;
        $list->addEntity($entity);

        $instance = new Identification($repository, $logger);
        $instance->discardIdentityCollection($list);

        $this->assertNotNull($entity->getStatus());
    }


    public function test_Changing_of_Password_for_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');

        $affected = new Entity\StandardIdentity;
        $affected->setId(99);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($repository, $logger);
        $instance->changePassword($affected, 'alpha', 'password');

        $this->assertTrue($affected->matchPassword('password'));
    }


    public function test_Failure_to_Change_of_Password_for_Identity()
    {
        $this->expectException(PasswordMismatch::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');

        $affected = new Entity\StandardIdentity;
        $affected->setId(99);
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance = new Identification($repository, $logger);
        $instance->changePassword($affected, 'wrong', 'password');
    }


    public function test_Blocking_of_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $instance = new Identification($repository, $logger);
        $instance->blockIdentity(new Entity\Identity);
    }


    public function test_Use_of_One_Time_Identity()
    {
        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->exactly(2))->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');

        $affected = new Entity\NonceIdentity;
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');
        $affected->setExpiresOn(time() + 1000);

        $instance = new Identification($repository, $logger);
        $this->assertInstanceOf(
            Entity\CookieIdentity::class,
            $instance->useNonceIdentity($affected, 'alpha')
        );
    }


    public function test_Use_of_Expired_One_Time_Identity()
    {
        $this->expectException(IdentityExpired::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();
        $repository->expects($this->once())->method('save');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('info');

        $affected = new Entity\NonceIdentity;
        $affected->setExpiresOn(1000);

        $instance = new Identification($repository, $logger);
        $instance->useNonceIdentity($affected, 'wrong');
    }


    public function test_Failure_to_Match_Key_of_One_Time_Identity()
    {
        $this->expectException(KeyMismatch::class);

        $repository = $this
                    ->getMockBuilder(Repository::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $logger->expects($this->once())->method('notice');

        $affected = new Entity\NonceIdentity;
        $affected->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');
        $affected->setExpiresOn(time() + 1000);

        $instance = new Identification($repository, $logger);
        $instance->useNonceIdentity($affected, 'wrong');
    }

}
