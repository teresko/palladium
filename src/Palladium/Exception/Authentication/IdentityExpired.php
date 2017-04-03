<?php

namespace Exception\Authentication;

use Palladium\Component\AppException as Exception;


class IdentityExpired extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.identity-expired';
}
