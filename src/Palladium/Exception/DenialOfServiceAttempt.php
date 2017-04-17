<?php

namespace Palladium\Exception;

use Palladium\Component\AppException as Exception;


class DenialOfServiceAttempt extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.dos-attempt';
}
