<?php

namespace Palladium\Service;

use PHPUnit\Framework\TestCase;


/**
 * @covers Palladium\Service\Registration
 */
final class RegistrationTest extends TestCase
{

    public function test_Creation_of_Email_Identity()
    {
        $instance = new Registration(
            $this->getMockBuilder('Palladium\Contract\CanCreateMapper')->getMock(),
            $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock()
        );
    }

}
