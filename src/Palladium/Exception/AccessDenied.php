<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class AccessDenied extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.access-denied';
}
