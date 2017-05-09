<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class DenialOfServiceAttempt extends Exception
{
    protected $code = 0;
    protected $message = 'palladium.error.dos-attempt';
}
