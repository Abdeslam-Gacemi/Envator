<?php

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\ResolverInterface;
use InvalidArgumentException;

class Resolver implements ResolverInterface
{
    /** @var array */
    protected $filepaths = [];

    public function setFilepaths(array $filepaths): ResolverInterface
    {
        $this->filepaths = $filepaths;
        return $this;
    }

    public function getFilepaths(): array
    {
        return $this->filepaths;
    }

    public function resolve()
    {
        foreach ($this->filepaths as $filepath) {
            if (!is_string($filepath) || !file_exists($filepath)) {
                throw new InvalidArgumentException("The file $filepath was not found.");
            }
            if (!is_readable($filepath)) {
                throw new InvalidArgumentException("The file $filepath is not readable.");
            }
        }
    }
}
