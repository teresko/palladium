<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class PasswordNotMatch extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.password-not-match';
}
