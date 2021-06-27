<?php

namespace Abdeslam\Envator\Contracts;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidCacheItemException extends Exception implements InvalidArgumentException
{
    # code...
}
