<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\InvalidCookieToken;

/**
 * @covers Palladium\Entity\Identity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class IdentityTest extends TestCase
{

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_Id($number, $expected)
    {
        $instance = new Identity;
        $instance->setId($number);

        $this->assertSame($expected, $instance->getId());
    }


    public function Provide_Assignment_of_Numeric()
    {
        return [
            [234, 234],
            ['1234', 1234],
            [0, 0],
            ['0', 0],
        ];
    }


    /**
     * @test
     * @dataProvider Provide_Invalid_Numeric_Value
     */
    public function Fail_Assigning_nonInt_as_Id($param)
    {
        $this->expectException(\TypeError::class);

        $instance = new Identity;
        $instance->setId($param);
    }

    public function Provide_Invalid_Numeric_Value()
    {
        return [
            [null],
            [''],
            ['alpha'],
        ];
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_ParentId($number, $expected)
    {
        $instance = new Identity;
        $instance->setParentId($number);

        $this->assertSame($expected, $instance->getParentId());
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_AccountId($number, $expected)
    {
        $instance = new Identity;
        $instance->setAccountId($number);

        $this->assertSame($expected, $instance->getAccountId());
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_Status_Change_Timestamp($number, $expected)
    {
        $instance = new Identity;
        $instance->setStatusChangedOn($number);

        $this->assertSame($expected, $instance->getStatusChangedOn());
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_ExpiresOn_Timestamp($number, $expected)
    {
        $instance = new Identity;
        $instance->setExpiresOn($number);

        $this->assertSame($expected, $instance->getExpiresOn());
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_Token_EoL_Timestamp($number, $expected)
    {
        $instance = new Identity;
        $instance->setTokenEndOfLife($number);

        $this->assertSame($expected, $instance->getTokenEndOfLife());
    }

    /**
     * @test
     * @dataProvider Provide_Assignment_of_Numeric
     */
    public function Assignment_of_LastUsed_Timestamp($number, $expected)
    {
        $instance = new Identity;
        $instance->setLastUsed($number);

        $this->assertSame($expected, $instance->getLastUsed());
    }

    /** @test */
    public function Assignment_of_Invalid_Token()
    {
        $this->expectException(\Palladium\Exception\InvalidToken::class);
        $instance = new Identity;
        $instance->setToken('alpha');
    }

    /** @test */
    public function Assignment_of_Token()
    {
        $instance = new Identity;

        $this->assertNull($instance->getToken());

        $instance->setToken('12345678901234567890123456789012');
        $this->assertSame('12345678901234567890123456789012', $instance->getToken());

        $instance->setToken(null);
        $this->assertNull($instance->getToken());
    }

    /** @test */
    public function Generation_of_New_Random_Token()
    {
        $instance = new Identity;

        $instance->generateToken();
        $this->assertNotNull($instance->getToken());
    }

    /** @test */
    public function Initialization_of_Status_Change_Timestamp()
    {
        $instance = new Identity;
        $this->assertNull($instance->getStatusChangedOn());

        $instance->setStatus(3);
        $this->assertNotNull($instance->getStatusChangedOn());

        $instance->setStatusChangedOn(1234);
        $instance->setStatus(3);
        $this->assertSame(1234, $instance->getStatusChangedOn());

        $instance->setStatus(100);
        $this->assertNotSame(1234, $instance->getStatusChangedOn());
    }

    /** @test */
    public function Assignment_of_Token_Action()
    {
        $instance = new Identity;

        $this->assertSame(Identity::ACTION_NONE, $instance->getTokenAction());

        $instance->setTokenAction(Identity::ACTION_VERIFY);
        $this->assertSame(Identity::ACTION_VERIFY, $instance->getTokenAction());

        $instance->setTokenAction(Identity::ACTION_NONE);
        $this->assertSame(Identity::ACTION_NONE, $instance->getTokenAction());

        $instance->setTokenAction('42');
        $this->assertSame(42, $instance->getTokenAction());

        $instance->setTokenAction(-100);
        $this->assertSame(Identity::ACTION_NONE, $instance->getTokenAction());

        $instance->setTokenAction(Identity::ACTION_NONE);
        $this->assertSame(Identity::ACTION_NONE, $instance->getTokenAction());
    }

    /** @test */
    public function Clearing_of_Token()
    {
        $instance = new Identity;
        $instance->generateToken();
        $instance->setTokenAction(Identity::ACTION_VERIFY);

        $instance->clearToken();

        $this->assertNull($instance->getToken());
        $this->assertNull($instance->getTokenEndOfLife());
        $this->assertSame(Identity::ACTION_NONE, $instance->getTokenAction());
        $this->assertNull($instance->getTokenPayload());
    }
}
