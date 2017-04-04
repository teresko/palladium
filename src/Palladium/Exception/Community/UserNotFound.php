<?php

namespace Palladium\Exception\Community;

use Palladium\Component\AppException as Exception;


class UserNotFound extends Exception
{
    protected $code = 0;
    protected $message = 'message.error.user-not-found';
}
