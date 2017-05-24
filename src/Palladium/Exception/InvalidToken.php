<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class InvalidToken extends Exception
{
    protected $code = 3005;
    protected $message = 'palladium.error.invalid-token';
}
