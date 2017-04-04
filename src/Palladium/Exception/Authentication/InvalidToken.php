<?php

namespace Palladium\Exception\Authentication;

use Palladium\Component\AppException as Exception;


class InvalidToken extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.invalid-token';
}
