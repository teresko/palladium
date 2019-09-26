<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

final class LoginWithPasswordTest extends TestCase
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
    public function Basic_Authentication_with_Identifier_and_Password()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('user.01@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(1, $cookie->getAccountId());
    }

    /** @test */
    public function Authentication_with_Identifier_and_Password_where_Hash_Cost_has_Changed()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('user.02@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $this->assertSame(2, $cookie->getAccountId());
    }

    /** @test */
    public function Using_Nonexistent_Identity_will_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityNotFound::class);
        $identity = $this->search->findStandardIdentityByIdentifier('fake@example.com');
    }

    /** @test */
    public function Wrong_Password_for_Existing_Identity_will_Cause_an_Exception()
    {
        $this->expectException(Exception\PasswordMismatch::class);

        $identity = $this->search->findStandardIdentityByIdentifier('user.02@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'wrong password');
    }
}
