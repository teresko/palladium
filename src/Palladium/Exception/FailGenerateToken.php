<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class FailGenerateToken extends Exception
{
    protected $code = 3030;
    protected $message = 'palladium.error.fail-provide-entropy-level';
}
