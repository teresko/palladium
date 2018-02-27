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
    public function test_Identifier_Assignment_Type_Cast()
    {
        $instance = new StandardIdentity;

        $instance->setIdentifier('alpha');
        $this->assertSame('alpha', $instance->getIdentifier());

        $instance->setIdentifier(12345);
        $this->assertSame('12345', $instance->getIdentifier());
    }


    public function test_Retrieval_of_Fingerprint()
    {
        $instance = new StandardIdentity;
        $instance->setIdentifier('alpha');

        $this->assertSame(
            '9cc3c0f06e170b14d7c52a8cbfc31bf9e4cc491e2aa9b79a385bcffa62f6bc619fcc95b5c1eb933dfad9c281c77208af',
            $instance->getFingerprint()
        );
    }


    public function test_Hash_Retrieval_for_a_Given_Key()
    {
        $instance = new StandardIdentity;

        $instance->setPassword('alpha');
        $this->assertTrue(password_verify('alpha', $instance->getHash()));
    }


    public function test_Hash_Assignment()
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


    public function test_for_Old_Hash()
    {
        $instance = new StandardIdentity;
        $instance->setHash('$1$beta$ocWFwI6Cax/SdMiwWXYoQ/');

        $this->assertTrue($instance->hasOldHash());


        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');
        $this->assertFalse($instance->hasOldHash());
    }

    public function test_Key_Matching()
    {
        $instance = new StandardIdentity;
        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $this->assertTrue($instance->matchPassword('alpha'));
    }


    public function test_Rehashing_of_a_Password()
    {
        $instance = new StandardIdentity;
        $instance->setPassword('alpha');
        $instance->setHash('$2y$12$P.92J1DVk8LXbTahB58QiOsyDg5Oj/PX0Mqa7t/Qx1Epuk0a4SehK');

        $instance->rehashPassword(10);

        $this->assertStringStartsWith('$2y$10', $instance->getHash());
    }
}
