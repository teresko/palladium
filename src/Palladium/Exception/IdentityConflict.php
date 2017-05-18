<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class IdentityConflict extends Exception
{
    protected $code = 3003;
    protected $message = 'palladium.error.identity-conflict';
}
