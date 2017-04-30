<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class UserNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.user-not-found';
}
