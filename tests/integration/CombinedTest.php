<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

/**
 * @coversNothing
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class CombinedTest extends TestCase
{

    private $registration;
    private $identification;
    private $search;
    private $recovery;

    private static $hold; // for passing data to the next test

    private $connection;

    public static function setUpBeforeClass()
    {
        copy(FIXTURE_PATH . '/integration.sqlite', FIXTURE_PATH . '/live.sqlite');
    }


    protected function setUp()
    {

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $connection = new PDO('sqlite:' . FIXTURE_PATH . '/live.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;

        $factory = new MapperFactory($connection, 'identities');


        $this->identification = new Identification($factory, $logger);
        $this->registration = new Registration($factory, $logger);
        $this->search = new Search($factory, $logger);
        $this->recovery = new Recovery($factory, $logger);
    }


    public function test_Account_Registration()
    {
        $identity = $this->registration->createEmailIdentity('test@example.com', 'password');
        $this->registration->bindAccountToIdentity(4, $identity);

        $this->assertSame(1, $identity->getId());

        self::$hold = $identity->getToken();
    }


    /**
     * @depends test_Account_Registration
     */
    public function test_Identify_Verification()
    {
        $token = self::$hold;

        $identity = $this->search->findEmailIdentityByToken($token, \Palladium\Entity\Identity::ACTION_VERIFY);
        $this->assertSame(\Palladium\Entity\Identity::STATUS_NEW, $identity->getStatus());

        $this->registration->verifyEmailIdentity($identity);
        $this->assertSame(\Palladium\Entity\Identity::STATUS_ACTIVE, $identity->getStatus());

        self::$hold = null;
    }


    /**
     * @depends test_Identify_Verification
     */
    public function test_User_Login_with_Password()
    {
        $identity = $this->search->findEmailIdentityByEmailAddress('test@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $cookie->getAccountId()); // from Registration phase

        self::$hold = [
            'account' => $cookie->getAccountId(),
            'series' => $cookie->getSeries(),
            'key' => $cookie->getKey(),
        ];
    }


    /**
     * @depends test_User_Login_with_Password
     */
    public function test_User_Login_with_Cookie()
    {
        $parts = self::$hold;

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $cookie = $this->identification->loginWithCookie($identity, $parts['key']);

        $this->assertSame(4, $cookie->getAccountId()); // from Registration phase
        $this->assertSame(1, $cookie->getParentId()); // from Registration phase

        self::$hold = [
            'account' => $cookie->getAccountId(),
            'series' => $cookie->getSeries(),
            'key' => $cookie->getKey(),
        ];
    }


    /**
     * @depends test_User_Login_with_Cookie
     */
    public function test_User_Logout()
    {
        $parts = self::$hold;

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $this->identification->logout($identity, $parts['key']);

        $this->assertSame(4, $identity->getAccountId()); // from Registration phase
    }


    /**
     * @depends test_User_Logout
     */
    public function test_User_Logout_Again()
    {
        $parts = self::$hold;

        $this->expectException(\Palladium\Exception\IdentityNotFound::class);

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $this->assertNull($identity->getId());

        self::$hold = null;
    }


    /**
     * @depends test_Identify_Verification
     */
    public function test_Requesting_New_Password()
    {
        $identity = $this->search->findEmailIdentityByEmailAddress('test@example.com');
        $token = $this->recovery->markForReset($identity);

        $this->assertNotNull($token);
        $this->assertSame($token, $identity->getToken());

        self::$hold = $token;
    }


    /**
     * @depends test_Requesting_New_Password
     */
    public function test_Setting_New_Password()
    {
        $token = self::$hold;

        $identity = $this->search->findEmailIdentityByToken($token, \Palladium\Entity\Identity::ACTION_RESET);
        $this->recovery->resetIdentityPassword($identity, 'foobar');

        $list = $this->search->findIdentitiesByParentId($identity->getId());
        $this->identification->discardIdentityCollection($list);

        $cookie = $this->identification->loginWithPassword($identity, 'foobar');
        $this->assertSame(4, $cookie->getAccountId());

        self::$hold = null;
    }


    /**
     * @depends test_Setting_New_Password
     */
    public function test_Changing_Password_for_Identity()
    {
        $identity = $this->search->findEmailIdentityByEmailAddress('test@example.com');
        $this->identification->changePassword($identity, 'foobar', 'password');

        $cookie = $this->identification->loginWithPassword($identity, 'password');
        $this->assertSame(4, $cookie->getAccountId());
    }


    public function test_Creating_One_Time_Use_Identity()
    {
        $identity = $this->registration->createNonceIdentity(4);
        $this->assertSame(4, $identity->getAccountId());

        self::$hold = [
            'identifier' => $identity->getIdentifier(),
            'key' => $identity->getKey(),
        ];
    }


    /**
     * @depends test_Creating_One_Time_Use_Identity
     */
    public function test_Using_the_One_Time_Identity()
    {
        $parts = self::$hold;

        $identity = $this->search->findNonceIdentityByIdentifier($parts['identifier']);
        $cookie = $this->identification->useNonceIdentity($identity, $parts['key']);

        $this->assertSame(4, $cookie->getAccountId());
    }


    /**
     * @depends test_Using_the_One_Time_Identity
     */
    public function test_Failure_to_Use_Same_One_Time_Identity_Twice()
    {
        $parts = self::$hold;

        $this->expectException(\Palladium\Exception\IdentityNotFound::class);

        $identity = $this->search->findNonceIdentityByIdentifier($parts['identifier']);
    }
}
