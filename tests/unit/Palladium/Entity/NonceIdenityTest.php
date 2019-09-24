<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\InvalidCookieToken;

/**
 * @covers Palladium\Entity\NonceIdentity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class NonceIdentityTest extends TestCase
{

    /** @test */
    public function  Gerneration_of_Nonce()
    {
        $instance = new NonceIdentity;
        $this->assertNull($instance->getIdentifier());
        $instance->generateNewNonce();
        $this->assertNotNull($instance->getIdentifier());
    }


    /** @test */
    public function  Gerneration_of_Key()
    {
        $instance = new NonceIdentity;
        $this->assertNull($instance->getKey());
        $instance->generateNewKey();
        $this->assertNotNull($instance->getKey());
    }


    /**
     * @test
     * @dataProvider Provide_Setting_of_a_Key
     */
    public function Setting_of_a_Key($input, $expected)
    {
        $instance = new NonceIdentity;
        $instance->setKey($input);
        $this->assertSame($expected, $instance->getKey());
    }


    public function Provide_Setting_of_a_Key()
    {
        return [
            [
                'input' => null,
                'expected' => null,
            ],
            [
                'input' => '',
                'expected' => null,
            ],
            [
                'input' => 234,
                'expected' => '234',
            ],
            [
                'input' => 'test',
                'expected' => 'test',
            ],
        ];
    }


    /** @test */
    public function Key_Matching()
    {
        $instance = new NonceIdentity;
        $instance->setHash('$2y$04$GPkwNpMWg6LguYHNuNUJSOQlpfdNKHfwu3HpkvyxkDfcIACifMOBu');

        $this->assertTrue($instance->matchKey('alpha'));
    }

}
