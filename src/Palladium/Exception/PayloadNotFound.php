<?php

namespace Palladium\Exception;

use Palladium\Component\Exception as Exception;

class PayloadNotFound extends Exception
{
    protected $code = 3010;
    protected $message = 'palladium.error.payload-not-found';
}
