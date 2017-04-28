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
        copy(FIXTURE_PATH . '/integration.sqlite', FIXTURE_PATH . '/live.sqlite');

        $connection = new PDO('sqlite:' . FIXTURE_PATH . '/live.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->registration = new Registration(
            new MapperFactory($connection, [
                'accounts' => [
                    'identities' => 'identities',
                ],
            ]),
            $this->getMockBuilder(LoggerInterface::class)->getMock()
        );
    }


    public function test_Initialization_of_Password_Reset_Process()
    {
        $identity = $this->registration->createEmailIdentity('test@example.com', 'password');
        $this->assertSame(1, $identity->getId());
    }


    protected function tearDown()
    {
        unlink(FIXTURE_PATH . '/live.sqlite');
    }
}
