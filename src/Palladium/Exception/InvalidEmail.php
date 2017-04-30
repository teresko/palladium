<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class InvalidEmail extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.invalid-email';
}
