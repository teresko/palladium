<?php

namespace Exception\Authentication;

use Palladium\Component\AppException as Exception;


class TokenNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.token-not-found';
}
