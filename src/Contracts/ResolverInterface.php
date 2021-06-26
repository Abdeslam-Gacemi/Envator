<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv\Contracts;

use Abdeslam\DotEnv\Exceptions\InvalidEnvFileException;

interface ResolverInterface
{
    /**
     * sets the .env files paths to resolve
     *
     * @param array $filepaths
     * @return ResolverInterface
     */
    public function setFilepaths(array $filepaths): ResolverInterface;

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