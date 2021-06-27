<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Contracts\ResolverInterface;
use Abdeslam\DotEnv\Exceptions\InvalidEnvFileException;

class Resolver implements ResolverInterface
{
    /** @var array */
    protected $filepaths = [];

    /**
     * @inheritDoc
     */
    public function setFilepaths(string ...$filepaths): ResolverInterface
    {
        $this->filepaths = $filepaths;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getFilepaths(): array
    {
        return $this->filepaths;
    }

    /**
     * @inheritDoc
     */
    public function resolve()
    {
        foreach ($this->filepaths as $filepath) {
            if (!is_string($filepath) || !file_exists($filepath)) {
                throw new InvalidEnvFileException("The file $filepath was not found. use absolute paths instead of relative paths.");
            }
            if (!is_readable($filepath)) {
                throw new InvalidEnvFileException("The file $filepath is not readable.");
            }
        }
    }
}
