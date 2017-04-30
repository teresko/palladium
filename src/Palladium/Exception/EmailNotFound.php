<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class EmailNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.email-not-found';
}
