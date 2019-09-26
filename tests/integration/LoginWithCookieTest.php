<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Component\MapperFactory;
use Palladium\Repository\Identity AS Repository;
use Palladium\Exception;
use Psr\Log\LoggerInterface;

use PDO;
use Mock;

final class LoginWithCookieTest extends TestCase
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
    public function Authentication_with_a_Cookie()
    {
        $identity = $this->search->findCookieIdentity(1, 'ebd5bd94383e0fd3e7a8eb90389e0b37');
        $result = $this->identification->loginWithCookie($identity, '3d5b7afdf32b24fd1bd38f08a4599afcc99883b491a300d1c97fb923dfde2616');

        $this->assertSame(1, $result->getAccountId());
        $this->assertSame(1, $result->getParentId());
    }

    /** @test */
    public function Authentication_with_a_Cookie_that_has_Expired_Cause_an_Exception()
    {
        $this->expectException(Exception\IdentityExpired::class);
        $identity = $this->search->findCookieIdentity(1, '823aea199313b38b0c3d27a57fc5238e');
        $this->identification->loginWithCookie($identity, '68d3e5a92599be95ac8287c9d39f5ffdf107b8c328bdf8edf726141353a43cc8');
    }

    /** @test */
    public function Authentication_with_a_Cookie_from_Password_Login()
    {
        $identity = $this->search->findStandardIdentityByIdentifier('user.01@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $identity = $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
        $result = $this->identification->loginWithCookie($identity, $cookie->getKey());

        $this->assertSame(1, $result->getAccountId());
        $this->assertSame(1, $result->getParentId());
    }

    /** @test */
    public function Attempt_to_Login_with_Wrong_Key_will_Cause_an_Exception()
    {
        $this->expectException(Exception\CompromisedCookie::class);

        $identity = $this->search->findStandardIdentityByIdentifier('user.01@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $identity = $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
        $this->identification->loginWithCookie($identity, '000000000');
    }

    /**
     * @test
     * @depends Attempt_to_Login_with_Wrong_Key_will_Cause_an_Exception
     */
    public function Attempt_to_Login_with_Wrong_Key_will_Logout_the_Entire_Series()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findStandardIdentityByIdentifier('user.01@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        try {
            $identity = $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
            $this->identification->loginWithCookie($identity, '000000000');
        } catch (Exception\CompromisedCookie $e) {
            // tested previously
        }

        $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
    }

    /** @test */
    public function After_Logout_the_Cookie_Series_is_Gone()
    {
        $this->expectException(Exception\IdentityNotFound::class);

        $identity = $this->search->findStandardIdentityByIdentifier('user.01@domain.tld');
        $cookie = $this->identification->loginWithPassword($identity, 'password');

        $identity = $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
        $this->identification->logout($identity, $cookie->getKey());

        $this->search->findCookieIdentity($cookie->getAccountId(), $cookie->getSeries());
    }
}
