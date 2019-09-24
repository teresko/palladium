<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception\IdentityNotFound;
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
        $repository = new Repository($factory);
        $mapper = $factory->create(\Palladium\Mapper\IdentityAccount::class);

        $cost = 4;

        $this->identification = new Identification($repository, $logger, 60, $cost);
        $this->recovery = new Recovery($repository, $logger, $cost);
        $this->registration = new Registration($repository, $mapper, $logger, $cost);
        $this->search = new Search($repository, $logger);
    }

    /** @test */
    public function Account_Registration()
    {
        $identity = $this->registration->createStandardIdentity('test@example.com', 'password');
        $this->registration->bindAccountToIdentity(4, $identity);

        $this->assertSame(2, $identity->getId());

        self::$hold = $identity->getToken();
    }

    /**
     * @test
     * @depends Account_Registration
     */
    public function Identify_Verification()
    {
        $token = self::$hold;

        $identity = $this->search->findStandardIdentityByToken($token, \Palladium\Entity\Identity::ACTION_VERIFY);
        $this->assertSame(\Palladium\Entity\Identity::STATUS_NEW, $identity->getStatus());

        $this->registration->verifyStandardIdentity($identity);
        $this->assertSame(\Palladium\Entity\Identity::STATUS_ACTIVE, $identity->getStatus());

        self::$hold = null;
    }

    /**
     * @test
     * @depends Identify_Verification
     */
    public function User_Login_with_Password()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('test@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $cookie->getAccountId()); // from Registration phase

        self::$hold = [
            'account' => $cookie->getAccountId(),
            'series' => $cookie->getSeries(),
            'key' => $cookie->getKey(),
        ];
    }

    /**
     * @test
     * @depends User_Login_with_Password
     */
    public function User_Login_with_Cookie()
    {
        $parts = self::$hold;

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $cookie = $this->identification->loginWithCookie($identity, $parts['key']);

        $this->assertSame(4, $cookie->getAccountId()); // from Registration phase
        $this->assertSame(2, $cookie->getParentId()); // from Registration phase

        self::$hold = [
            'account' => $cookie->getAccountId(),
            'series' => $cookie->getSeries(),
            'key' => $cookie->getKey(),
        ];
    }

    /**
     * @test
     * @depends User_Login_with_Cookie
     */
    public function User_Logout()
    {
        $parts = self::$hold;

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $this->identification->logout($identity, $parts['key']);

        $this->assertSame(4, $identity->getAccountId()); // from Registration phase
    }

    /**
     * @test
     * @depends User_Logout
     */
    public function User_Logout_Again()
    {
        $parts = self::$hold;

        $this->expectException(\Palladium\Exception\IdentityNotFound::class);

        $identity = $this->search->findCookieIdentity($parts['account'], $parts['series']);
        $this->assertNull($identity->getId());

        self::$hold = null;
    }

    /**
     * @test
     * @depends Identify_Verification
     */
    public function Requesting_New_Password()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('test@example.com');
        $token = $this->recovery->markForReset($identity);

        $this->assertNotNull($token);
        $this->assertSame($token, $identity->getToken());

        self::$hold = $token;
    }

    /**
     * @test
     * @depends Requesting_New_Password
     */
    public function Setting_New_Password()
    {
        $token = self::$hold;

        $identity = $this->search->findStandardIdentityByToken($token, \Palladium\Entity\Identity::ACTION_RESET);
        $this->recovery->resetIdentityPassword($identity, 'foobar');

        $list = $this->search->findIdentitiesByParentId($identity->getId());
        $this->identification->discardIdentityCollection($list);

        $cookie = $this->identification->loginWithPassword($identity, 'foobar');
        $this->assertSame(4, $cookie->getAccountId());

        self::$hold = null;
    }

    /**
     * @test
     * @depends Setting_New_Password
     */
    public function Changing_Password_for_Identity()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('test@example.com');
        $this->identification->changePassword($identity, 'foobar', 'foobuz');

        $cookie = $this->identification->loginWithPassword($identity, 'foobuz');
        $this->assertSame(4, $cookie->getAccountId());
    }

    /**
     * @test
     * @depends Changing_Password_for_Identity
     */
    public function Changing_Password_for_Identity_By_Id()
    {
        $identity = $this->search->findStandardIdentityById(2);
        $this->identification->changePassword($identity, 'foobuz', 'password');

        $cookie = $this->identification->loginWithPassword($identity, 'password');
        $this->assertSame(4, $cookie->getAccountId());
    }


    /** @test */
    public function Creating_One_Time_Use_Identity()
    {
        $identity = $this->registration->createNonceIdentity(4);
        $this->assertSame(4, $identity->getAccountId());

        self::$hold = [
            'identifier' => $identity->getIdentifier(),
            'key' => $identity->getKey(),
        ];
    }

    /**
     * @test
     * @depends Creating_One_Time_Use_Identity
     */
    public function Using_the_One_Time_Identity()
    {
        $parts = self::$hold;

        $identity = $this->search->findNonceIdentityByIdentifier($parts['identifier']);
        $cookie = $this->identification->useNonceIdentity($identity, $parts['key']);

        $this->assertSame(4, $cookie->getAccountId());
    }

    /**
     * @test
     * @depends Using_the_One_Time_Identity
     */
    public function Failure_to_Use_Same_One_Time_Identity_Twice()
    {
        $parts = self::$hold;

        $this->expectException(\Palladium\Exception\IdentityNotFound::class);

        $this->search->findNonceIdentityByIdentifier($parts['identifier']);
    }

    /**
     * @test
     * @depends Using_the_One_Time_Identity
     * @depends Identify_Verification
     */
    public function FetchMode_Changed_for_PDO()
    {
        $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);

        $identity = $this->search->findStandardIdentityByIdentifier('test@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $cookie->getAccountId()); // from Registration phase

        self::$hold = [
            'account' => $cookie->getAccountId(),
            'series' => $cookie->getSeries(),
            'key' => $cookie->getKey(),
        ];
    }

    /** @test */
    public function Rehashing_of_Outdated_Password_on_Login()
    {
        // using preexisting entry in sqlite database

        $repository = new Repository(new MapperFactory($this->connection, 'identities'));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $identification = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN, 5);

        $identity = $this->search->findStandardIdentityByIdentifier('foobar@who.cares');
        $this->assertStringStartsWith('$2y$04', $identity->getHash());

        $identification->loginWithPassword($identity, 'qwerty');

        $affected = $this->search->findStandardIdentityByIdentifier('foobar@who.cares');
        $this->assertGreaterThan(1496353300, $affected->getLastUsed());
        $this->assertStringStartsWith('$2y$05', $affected->getHash());
    }

    /**
     * @test
     * @depends Rehashing_of_Outdated_Password_on_Login
     */
    public function Logging_in_After_Password_has_been_Updated()
    {
        $repository = new Repository(new MapperFactory($this->connection, 'identities'));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $identification = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN, 5);

        $identity = $this->search->findStandardIdentityByIdentifier('foobar@who.cares');
        $cookie = $this->identification->loginWithPassword($identity, 'qwerty');
        $this->assertSame(9, $cookie->getAccountId());
    }

    /**
     * @test
     * @depends Account_Registration
     */
    public function Finding_Identity_By_Id()
    {
        $repository = new Repository(new MapperFactory($this->connection, 'identities'));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $identification = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN,  11);

        $identity = $this->search->findStandardIdentityById(2);
        $this->assertSame('test@example.com', $identity->getIdentifier());
    }

    /**
     * @test
     * @depends Finding_Identity_By_Id
     */
    public function Initialize_Modification_of_Standard_Identity()
    {
        $repository = new Repository(new MapperFactory($this->connection, 'identities'));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $identification = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN,  11);

        $identity = $this->search->findStandardIdentityById(2);
        $token = $identification->markForUpdate($identity, [
            'identifier' => 'new@example.com'
        ]);

        $identity = $this->search->findStandardIdentityByToken($token, \Palladium\Entity\Identity::ACTION_UPDATE);
        $this->assertSame('test@example.com', $identity->getIdentifier());

        $identification->loginWithPassword($identity, 'password');
        $identification->applyTokenPayload($identity);

        $identity = $this->search->findStandardIdentityById(2);
        $this->assertSame('new@example.com', $identity->getIdentifier());
    }

    /**
     * @test
     * @depends Initialize_Modification_of_Standard_Identity
     */
    public function Removing_Existing_Identity()
    {
        $repository = new Repository(new MapperFactory($this->connection, 'identities'));
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $identification = new Identification($repository, $logger, Identification::DEFAULT_COOKIE_LIFESPAN,  11);

        $identity = $this->search->findIdentityById(2);
        $identification->deleteIdentity($identity);

        $this->expectException(IdentityNotFound::class);

        $this->search->findIdentityById(2);
    }

    /**
     * @test
     * @depends Removing_Existing_Identity
     */
    public function Account_with_Case_Insensitive_Identifier()
    {
        $identity = $this->registration->createStandardIdentity('foo.BaR@example.com', 'password');
        $this->registration->bindAccountToIdentity(4, $identity);

        $identity = $this->search->findStandardIdentityByIdentifier('FOO.bar@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $cookie->getAccountId());
    }
}
