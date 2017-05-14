<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class IdentityMalformed extends Exception
{
    protected $code = 3007;
    protected $message = 'palladium.error.identity-malformed';
}
