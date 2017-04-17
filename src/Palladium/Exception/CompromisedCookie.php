<?php

namespace Palladium\Exception;

use Palladium\Component\AppException as Exception;


class CompromisedCookie extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.compromised-cookie';
}
