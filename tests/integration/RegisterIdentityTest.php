<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

final class RegisterIdentityTest extends TestCase
{
    private $registration;

    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $connection = new PDO('sqlite:' . sys_get_temp_dir() . '/db.sqlite');
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $factory = new MapperFactory($connection, 'identities');
        $repository = new Repository($factory);
        $mapper = $factory->create(\Palladium\Mapper\IdentityAccount::class);

        $this->registration = new Registration($repository, $mapper, $logger, 4);
        $this->search = new Search($repository, $logger);
    }

    /** @test */
    public function Creating_a_New_Standard_Identity()
    {
        $identity = $this->registration->createStandardIdentity('test.01@example.com', 'password');
        $this->registration->bindAccountToIdentity(4, $identity);

        $result = $this->search->findStandardIdentityByIdentifier('test.01@example.com');
        $this->assertSame(4, $result->getAccountId());
        $this->assertSame(\Palladium\Entity\Identity::STATUS_NEW, $result->getStatus());
    }

    /** @test */
    public function Attempting_to_Create_a_Standard_Identity_with_Same_Identifier_will_Cause_Exception()
    {
        $this->expectException(Exception\IdentityConflict::class);

        $identity = $this->registration->createStandardIdentity('test.01@example.com', 'password');
    }

    /** @test */
    public function Attempting_to_Bypass_Identifier_Uniqueness_Check_will_Cause_Exception()
    {
        $this->expectException(Exception\IdentityConflict::class);

        $identity = $this->registration->createStandardIdentity('TEST.01@example.com', 'password');
    }
}
