<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class InvalidToken extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.invalid-token';
}
