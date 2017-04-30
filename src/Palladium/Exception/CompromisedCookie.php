<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class CompromisedCookie extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.compromised-cookie';
}
