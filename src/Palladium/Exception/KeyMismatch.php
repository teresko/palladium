<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class KeyMismatch extends Exception
{
    protected $code = 3010;
    protected $message = 'palladium.error.key-mismatch';
}
