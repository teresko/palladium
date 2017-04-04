<?php

namespace Palladium\Exception\Authentication;

use Palladium\Component\AppException as Exception;


class IdentityNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.identity-not-found';
}
