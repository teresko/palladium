<?php

namespace Palladium\Entity\Authentication;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\Authentication\InvalidCookieToken;

/**
 * @covers Palladium\Entity\Authentication\CookieIdentity
 */
final class CookieIdentityTest extends TestCase
{

    public function test_Collapsed_Value_for_Empty_Identity()
    {
        $instance = new CookieIdentity;
        $this->assertNull($instance->getCollapsedValue());
    }


    /**
     * @dataProvider provide_Assignment_of_Params
     */
    public function test_Assignment_of_Series($param, $expected)
    {
        $instance = new CookieIdentity;

        $instance->setSeries($param);
        $this->assertSame($expected, $instance->getSeries());
    }


    public function provide_Assignment_of_Params()
    {
        return [
            ['', null],
            [234, '234'],
            [0, '0'],
            ['alpha', 'alpha'],
        ];
    }


    /**
     * @dataProvider provide_Assignment_of_Params
     */
    public function test_Assignment_of_Key($param, $expected)
    {
        $instance = new CookieIdentity;

        $instance->setKey($param);
        $this->assertSame($expected, $instance->getKey());
    }


    /**
     * @dataProvider provide_Assignment_of_Params
     */
    public function test_Assignment_of_Hash($param, $expected)
    {
        $instance = new CookieIdentity;

        $instance->setHash($param);
        $this->assertSame($expected, $instance->getHash());
    }


    public function test_Collapsed_Value_for_Populated_Identity()
    {
        $instance = new CookieIdentity;
        $instance->setId(1);
        $instance->setUserId(42);
        $instance->setSeries('alpha');
        $instance->setKey('beta');
        $this->assertSame('42|alpha|beta', $instance->getCollapsedValue());
    }


    public function test_Data_Extraction_from_Collapsed_Value_when_Null()
    {
        $this->expectException(InvalidCookieToken::class);

        $instance = new CookieIdentity;
        $instance->setCollapsedValue(null);
    }


    public function test_Data_Extraction_from_Collapsed_Value_when_Random_Crap()
    {
        $this->expectException(InvalidCookieToken::class);

        $instance = new CookieIdentity;
        $instance->setCollapsedValue('asdasdasd');
    }


    public function test_Data_Extraction_from_Collapsed_Value_when_Correct_Token()
    {
        $instance = new CookieIdentity;
        $instance->setCollapsedValue('42|alpha|beta');

        $this->assertSame(42, $instance->getUserId());
        $this->assertSame('alpha', $instance->getSeries());
        $this->assertSame('beta', $instance->getKey());
    }


    public function test_Retrieval_of_Fingerprint()
    {
        $instance = new CookieIdentity;
        $instance->setSeries('alpha');

        $this->assertSame(
            '9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af',
            $instance->getFingerprint()
        );
    }


    public function test_hash_Creation_and_Verification_Integriety()
    {
        $instance = new CookieIdentity;
        $instance->setKey('alpha');

        $generated = $instance->getHash();

        $this->assertTrue($generated);
    }
}
