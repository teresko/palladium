<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Psr\Log\LoggerInterface;
use PDO;

/**
 * @covers Palladium\Service\Registration
 */
final class CombinedTest extends TestCase
{

    private $registration;



    protected function setUp()
    {
        if (file_exists(FIXTURE_PATH . '/live.sqlite')) {
            unlink(FIXTURE_PATH . '/live.sqlite');
        }
        copy(FIXTURE_PATH . '/integration.sqlite', FIXTURE_PATH . '/live.sqlite');

        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $connection = new PDO('sqlite:' . FIXTURE_PATH . '/live.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $factory = new MapperFactory($connection, [
            'accounts' => [
                'identities' => 'identities',
            ],
        ]);


        $this->registration = new Registration($factory, $logger);
    }


    public function test_Initialization_of_Password_Reset_Process()
    {
        $identity = $this->registration->createEmailIdentity('test@example.com', 'password');
        $this->assertSame(1, $identity->getId());

        $token = $identity->getToken();


        $identity = $this->registration->verifyEmailIdentity($token);
        $this->assertSame(1, $identity->getId());
    }
}
