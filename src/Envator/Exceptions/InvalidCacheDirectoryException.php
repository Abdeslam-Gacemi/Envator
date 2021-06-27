<?php

namespace Abdeslam\Envator\Exceptions;

use Exception;
use Psr\SimpleCache\InvalidArgumentException;

class InvalidCacheDirectoryException extends Exception implements InvalidArgumentException
{
    # code...
}
