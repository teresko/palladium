<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @covers Palladium\Entity\StandardIdentity
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class StandardIdentityTest extends TestCase
{
    /** @test */
    public function Identifier_Assignment_Type_Cast()
    {
        $instance = new StandardIdentity;

        $instance->setIdentifier('alpha');
        $this->assertSame('alpha', $instance->getIdentifier());

        $instance->setIdentifier(12345);
        $this->assertSame('12345', $instance->getIdentifier());
    }


    /** @test */
    public function Retrieval_of_Fingerprint()
    {
        $instance = new StandardIdentity;
        $instance->setIdentifier('alpha');

        $this->assertSame(
            '9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af',
            $instance->getFingerprint()
        );
    }


    /** @test */
    public function Hash_Retrieval_for_a_Given_Key()
    {
        $instance = new StandardIdentity;

        $instance->setPassword('alpha', 4);
        $this->assertTrue(password_verify('alpha', $instance->getHash()));
    }


    /** @test */
    public function Hash_Assignment()
    {
        $instance = new StandardIdentity;
        $this->assertNull($instance->getHash());

        $instance->setHash('alpha');
        $this->assertSame('alpha', $instance->getHash());

        $instance->setHash(12345);
        $this->assertSame('12345', $instance->getHash());

        $instance->setHash(null);
        $this->assertNull($instance->getHash());
    }


    /** @test */
    public function Check_for_Old_Hash()
    {
        $instance = new StandardIdentity;
        $instance->setHash('$1$beta$ocWFwI6Cax/SdMiwWXYoQ/');

        $this->assertTrue($instance->hasOldHash());


        $instance->setHash('$2y$04$GPkwNpMWg6LguYHNuNUJSOQlpfdNKHfwu3HpkvyxkDfcIACifMOBu');
        $this->assertFalse($instance->hasOldHash(4));
    }

    /** @test */
    public function Key_Matching()
    {
        $instance = new StandardIdentity;
        $instance->setHash('$2y$04$GPkwNpMWg6LguYHNuNUJSOQlpfdNKHfwu3HpkvyxkDfcIACifMOBu');

        $this->assertTrue($instance->matchPassword('alpha'));
    }


    /** @test */
    public function Rehashing_of_a_Password()
    {
        $instance = new StandardIdentity;
        $instance->setPassword('alpha');
        $instance->setHash('$2y$04$GPkwNpMWg6LguYHNuNUJSOQlpfdNKHfwu3HpkvyxkDfcIACifMOBu');

        $instance->rehashPassword(5);

        $this->assertStringStartsWith('$2y$05', $instance->getHash());
    }
}
