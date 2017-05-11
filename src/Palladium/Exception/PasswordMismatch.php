<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class PasswordMismatch extends Exception
{
    protected $code = 0;
    protected $message = 'palladium.error.password-mismatch';
}
