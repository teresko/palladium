<?php

namespace Palladium\Exception\Authentication;

use Palladium\Component\AppException as Exception;


class AccessDenied extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.access-denied';
}
