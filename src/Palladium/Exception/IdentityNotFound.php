<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class IdentityNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'palladium.error.identity-not-found';
}
