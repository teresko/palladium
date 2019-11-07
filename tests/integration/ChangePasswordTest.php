<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

final class ChangePasswordTest extends TestCase
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
        $this->recovery = new Recovery($repository, $logger, 4);
    }

    /** @test */
    public function Attempting_to_Change_Password_with_Fake_Token_will_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findStandardIdentityByToken(
            '00000000000000000000000000000000',
            \Palladium\Entity\Identity::ACTION_RESET
        );
        $this->recovery->resetIdentityPassword($identity, 'bad');
    }

    /** @test */
    public function Attempting_to_Change_Password_for_Unverified_Identity_will_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findStandardIdentityByToken(
            'a133d26d5529bed304220e6782034e4d',
            \Palladium\Entity\Identity::ACTION_RESET
        );
        $this->recovery->resetIdentityPassword($identity, 'bad');
    }

    /** @test */
    public function Change_Password_with_Prepared_Token()
    {
        $identity = $this->search->findStandardIdentityByToken(
            '4e93e507366e4f7d5a7b841741cb9d86',
            \Palladium\Entity\Identity::ACTION_RESET
        );
        $this->recovery->resetIdentityPassword($identity, 'foobar');

        $identity = $this->search->findStandardIdentityByIdentifier('user.04@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'foobar');

        $this->assertSame(1, $cookie->getAccountId());
    }

    /** @test */
    public function Attempting_to_Change_Password_with_Used_Token_will_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findStandardIdentityByToken(
            '4e93e507366e4f7d5a7b841741cb9d86',
            \Palladium\Entity\Identity::ACTION_RESET
        );
        $this->recovery->resetIdentityPassword($identity, 'foobar');
    }

    /** @test */
    public function Attempting_to_Mark_for_Password_Change_of_Unverified_Identity_will_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityNotVerified::class);

        $this->recovery->markForReset(
            $this->search->findStandardIdentityById(6)
        );
    }

    /** @test */
    public function Marking_of_Identity_for_Password_Reset()
    {
        $generatedToken = $this->recovery->markForReset(
            $this->search->findStandardIdentityById(9)
        );

        $identity = $this->search->findStandardIdentityByToken(
            $generatedToken,
            \Palladium\Entity\Identity::ACTION_RESET
        );

        $this->assertSame(9, $identity->getId());
    }

    /**
     * @test
     * @depends Marking_of_Identity_for_Password_Reset
     */
    public function Second_Time_Marking_of_Identity_for_Password_Reset_will_Override_the_Old_Token()
    {
        $generatedToken = $this->recovery->markForReset(
            $this->search->findStandardIdentityById(9)
        );

        $identity = $this->search->findStandardIdentityByToken(
            $generatedToken,
            \Palladium\Entity\Identity::ACTION_RESET
        );

        $this->assertSame(9, $identity->getId());
    }
}
