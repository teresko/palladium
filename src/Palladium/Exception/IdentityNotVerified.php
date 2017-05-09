<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class IdentityNotVerified extends Exception
{
    protected $code = 0;
    protected $message = 'palladium.error.identity-not-verified';
}
