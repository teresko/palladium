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

    private static $hold; // for passing data to the next test

    private $connection;

    public static function setUpBeforeClass()
    {
        copy(FIXTURE_PATH . '/integration.sqlite', FIXTURE_PATH . '/live.sqlite');
    }

    public static function tearDownAfterClass()
    {
        // unlink(FIXTURE_PATH . '/integration.sqlite', FIXTURE_PATH . '/live.sqlite');
    }

    protected function setUp()
    {

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $connection = new PDO('sqlite:' . FIXTURE_PATH . '/live.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->connection = $connection;

        $factory = new MapperFactory($connection, [
            'accounts' => [
                'identities' => 'identities',
            ],
        ]);


        $this->identification = new Identification($factory, $logger);
        $this->registration = new Registration($factory, $logger);
        $this->search = new Search($factory, $logger);
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
    public function test_identify_Verification()
    {
        $token = self::$hold;

        $identity = $this->registration->verifyEmailIdentity($token);
        $this->assertSame(1, $identity->getId());

        self::$hold = null;
    }


    /**
     * @depends test_identify_Verification
     */
    public function test_User_Login_with_Password()
    {
        $identity = $this->search->findEmailIdenityByIdentifier('test@example.com');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(4, $identity->getUserId()); // from Registration phase

        self::$hold = $identity->getCollapsedValue();
    }
}
