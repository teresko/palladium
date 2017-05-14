<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class InvalidCookieToken extends Exception
{
    protected $code = 3006;
    protected $message = 'palladium.error.invalid-cookie-token';
}
