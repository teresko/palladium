<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class CompromisedCookie extends Exception
{
    protected $code = 3009;
    protected $message = 'palladium.error.compromised-cookie';
}
