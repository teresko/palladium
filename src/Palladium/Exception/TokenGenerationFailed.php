<?php

namespace Palladium\Exception;

use RuntimeException as Exception;

class TokenGenerationFailed extends Exception
{
    protected $code = 3030;
    protected $message = 'palladium.error.missing-randomness-source';
}
