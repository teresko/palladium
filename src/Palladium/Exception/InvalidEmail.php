<?php

namespace Palladium\Exception;

use Palladium\Component\AppException as Exception;


class InvalidEmail extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.invalid-email';
}
