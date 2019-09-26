<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

final class LoginWithNonceTokenTest extends TestCase
{
    private $identification;
    private $search;

    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $connection = new PDO('sqlite:' . sys_get_temp_dir() . '/db.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $factory = new MapperFactory($connection, 'identities');
        $repository = new Repository($factory);

        $this->identification = new Identification($repository, $logger, 60, 4);
        $this->search = new Search($repository, $logger);
    }

    /** @test */
    public function Authentication_with_Nonce_Token_that_has_Wrong_Key_will_Cause_Exception()
    {
        $this->expectException(Exception\KeyMismatch::class);

        $identity = $this->search->findNonceIdentityByIdentifier('60e2962c974f8cad3bd3cb9b8106ed22');
        $cookie = $this->identification->useNonceIdentity($identity, '0000000000000000000000000000000000000000000000000000000000000000');
    }

    /** @test */
    public function Authentication_with_Nonce_Token()
    {
        $identity = $this->search->findNonceIdentityByIdentifier('60e2962c974f8cad3bd3cb9b8106ed22');
        $cookie = $this->identification->useNonceIdentity($identity, '7fd8d3575e6c9bbf368fe19db91d1646694ad5d98b10aef5fd80b78d84c770b2');

        $this->assertSame(1, $cookie->getAccountId());
    }

    /**
     * @test
     * @depends Authentication_with_Nonce_Token
     */
    public function Reusing_Same_Nonce_Pair_will_Fail_with_Exception()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findNonceIdentityByIdentifier('60e2962c974f8cad3bd3cb9b8106ed22');
    }
}
