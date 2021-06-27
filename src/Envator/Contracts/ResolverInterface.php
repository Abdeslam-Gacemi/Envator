<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\Envator\Contracts;

use Abdeslam\Envator\Exceptions\InvalidEnvFileException;

interface ResolverInterface
{
    /**
     * sets the .env files paths to resolve
     *
     * @param array $filepaths
     * @return ResolverInterface
     */
    public function setFilepaths(string ...$filepaths): ResolverInterface;

    /**
     * gets the array of .env files paths
     *
     * @return array
     */
    public function getFilepaths(): array;

    /**
     * resolves the .env files paths
     *
     * @return void
     * @throws InvalidEnvFileException
     */
    public function resolve();
}
