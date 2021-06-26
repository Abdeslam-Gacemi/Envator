<?php

namespace Abdeslam\DotEnv\Contracts;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidCacheItemException extends Exception implements InvalidArgumentException
{
    # code...
}
