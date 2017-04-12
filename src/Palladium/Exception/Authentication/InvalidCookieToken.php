<?php

namespace Palladium\Exception\Authentication;

use Palladium\Component\AppException as Exception;


class InvalidCookieToken extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.invalid-cookie-token';
}
