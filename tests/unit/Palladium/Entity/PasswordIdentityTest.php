<?php

namespace Palladium\Entity;

use PHPUnit\Framework\TestCase;
use Palladium\Exception\InvalidPassword;
use Palladium\Exception\InvalidEmail;

/**
 * @covers Palladium\Entity\PasswordIdentity
 */
final class PasswordIdentityTest extends TestCase
{
    public function test_Identifier_Assignment_Type_Cast()
    {
        $instance = new PasswordIdentity;

        $instance->setIdentifier('alpha');
        $this->assertSame('alpha', $instance->getIdentifier());

        $instance->setIdentifier(12345);
        $this->assertSame('12345', $instance->getIdentifier());
    }


    public function test_Retrieval_of_Fingerprint()
    {
        $instance = new PasswordIdentity;
        $instance->setIdentifier('alpha');

        $this->assertSame(
            '9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af',
            $instance->getFingerprint()
        );
    }


    public function test_Hash_Retrieval_for_a_Given_Key()
    {
        $instance = new PasswordIdentity;

        $instance->setPassword('alpha');
        $this->assertTrue(password_verify('alpha', $instance->getHash()));
    }


    public function test_Hash_Assignment()
    {
        $instance = new PasswordIdentity;
        $this->assertNull($instance->getHash());

        $instance->setHash('alpha');
        $this->assertSame('alpha', $instance->getHash());

        $instance->setHash(12345);
        $this->assertSame('12345', $instance->getHash());

        $instance->setHash(null);
        $this->assertNull($instance->getHash());
    }


    public function test_Validation_with_invalid_Email()
    {
        $this->expectException(InvalidEmail::class);

        $instance = new PasswordIdentity;
        $instance->setIdentifier('no.an.email');

        $instance->validate();
    }


    public function test_Validation_with_invalid_Password()
    {
        $this->expectException(InvalidPassword::class);

        $instance = new PasswordIdentity;
        $instance->setIdentifier('alpha@example.com');
        $instance->setPassword('bad');

        $instance->validate();
    }


    public function test_Successful_Validation()
    {
        $instance = new PasswordIdentity;
        $instance->setIdentifier('alpha@example.com');
        $instance->setPassword('password');

        $this->assertNull($instance->validate());
    }


    public function test_for_Old_Hash()
    {
        $instance = new PasswordIdentity;
        $instance->setHash('$1$beta$ocWFwI6Cax/SdMiwWXYoQ/');

        $this->assertTrue($instance->isOldHash());


        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');
        $this->assertFalse($instance->isOldHash());
    }

    public function test_Key_Matching()
    {
        $instance = new PasswordIdentity;
        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $this->assertTrue($instance->matchKey('alpha'));
    }
}
