<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\InvalidCookieToken;

/**
 * @covers Palladium\Entity\OneTimeIdentity
 */
final class OneTimeIdentityTest extends TestCase
{

    public function  test_Gerneration_of_Nonce()
    {
        $instance = new OneTimeIdentity;
        $this->assertNull($instance->getNonce());
        $instance->generateNewNonce();
        $this->assertNotNull($instance->getNonce());
    }


    public function  test_Gerneration_of_Key()
    {
        $instance = new OneTimeIdentity;
        $this->assertNull($instance->getKey());
        $instance->generateNewKey();
        $this->assertNotNull($instance->getKey());
    }


    /**
     * @dataProvider provide_Setting_of_a_Key
     */
    public function test_Setting_of_a_Key($input, $expected)
    {
        $instance = new OneTimeIdentity;
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
        $instance = new OneTimeIdentity;
        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $this->assertTrue($instance->matchKey('alpha'));
    }

}
