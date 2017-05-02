<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;


class AccountNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.account-not-found';
}
