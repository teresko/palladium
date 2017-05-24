<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\InvalidCookieToken;

/**
 * @covers Palladium\Entity\NonceIdentity
 */
final class NonceIdentityTest extends TestCase
{

    public function  test_Gerneration_of_Nonce()
    {
        $instance = new NonceIdentity;
        $this->assertNull($instance->getIdentifier());
        $instance->generateNewNonce();
        $this->assertNotNull($instance->getIdentifier());
    }


    public function  test_Gerneration_of_Key()
    {
        $instance = new NonceIdentity;
        $this->assertNull($instance->getKey());
        $instance->generateNewKey();
        $this->assertNotNull($instance->getKey());
    }


    /**
     * @dataProvider provide_Setting_of_a_Key
     */
    public function test_Setting_of_a_Key($input, $expected)
    {
        $instance = new NonceIdentity;
        $instance->setKey($input);
        $this->assertSame($expected, $instance->getKey());
    }


    public function provide_Setting_of_a_Key()
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


    public function test_Key_Matching()
    {
        $instance = new NonceIdentity;
        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $this->assertTrue($instance->matchKey('alpha'));
    }

}
