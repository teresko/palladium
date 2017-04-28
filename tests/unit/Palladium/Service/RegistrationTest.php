<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;

use Palladium\Exception\IdentityDuplicated;

/**
 * @covers Palladium\Service\Registration
 */
final class RegistrationTest extends TestCase
{

    public function test_Failure_of_Creating_Duplicate_Email_Identity()
    {
        $this->expectException(IdentityDuplicated::class);

        $factory = $this->getMockBuilder('Palladium\Contract\CanCreateMapper')->getMock();
        $factory->method('create')->will($this->returnValue(new \Mock\Mapper([
            'exists' => true,
        ])));


        $instance = new Registration(
            $factory,
            $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock()
        );

        $instance->createEmailIdentity('foo@example.com', 'password');
    }

}
