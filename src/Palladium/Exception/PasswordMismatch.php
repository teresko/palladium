<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class PasswordMismatch extends Exception
{
    protected $code = 3008;
    protected $message = 'palladium.error.password-mismatch';
}
