<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

/**
 * @coversNothing
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


    public function test_User_Registration()
    {
        $user = new Mock\User(4);

        $identity = $this->registration->createEmailIdentity('test@example.com', 'password');
        $this->registration->bindIdentityToUser($identity, $user);

        $this->assertSame(1, $identity->getId());

        self::$hold = $identity->getToken();
    }


    /**
     * @depends test_User_Registration
     */
    public function test_Identify_Verification()
    {
        $token = self::$hold;

        $identity = $this->registration->verifyEmailIdentity($token);
        $this->assertSame(1, $identity->getId());

        self::$hold = null;
    }


    /**
     * @depends test_Identify_Verification
     */
    public function test_User_Login_with_Password()
    {
        $identity = $this->search->findEmailIdenityByIdentifier('test@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $cookie->getUserId()); // from Registration phase

        self::$hold = [
            'user' => $cookie->getUserId(),
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

        $identity = $this->search->findCookieIdenity($parts['user'], $parts['series']);
        $cookie = $this->identification->loginWithCookie($identity, $parts['key']);

        $this->assertSame(4, $cookie->getUserId()); // from Registration phase

        self::$hold = [
            'user' => $cookie->getUserId(),
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

        $identity = $this->search->findCookieIdenity($parts['user'], $parts['series']);
        $this->identification->logout($identity, $parts['key']);

        $identity = $this->search->findCookieIdenity($parts['user'], $parts['series']);
        $this->assertNull($identity->getId());

        self::$hold = null;
    }


    /**
     * @depends test_Identify_Verification
     */
    public function test_Requesting_New_Password()
    {
        $identity = $this->search->findEmailIdenityByIdentifier('test@example.com');
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

        $identity = $this->search->findEmailIdenityByToken($token, \Palladium\Entity\Identity::ACTION_RESET);
        $this->recovery->resetIdentityPassword($identity, 'foobar');
        $this->identification->discardRelatedCookies($identity);

        $cookie = $this->identification->loginWithPassword($identity, 'foobar');
        $this->assertSame(4, $cookie->getUserId());

        self::$hold = null;
    }


    /**
     * @depends test_Setting_New_Password
     */
    public function test_Changing_Password_for_Identity()
    {
        $identity = $this->search->findEmailIdenityByIdentifier('test@example.com');
        $this->identification->changePassword($identity, 'foobar', 'password');

        $cookie = $this->identification->loginWithPassword($identity, 'password');
        $this->assertSame(4, $cookie->getUserId());
    }
}
