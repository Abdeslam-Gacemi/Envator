<?php

namespace Abdeslam\DotEnv\Contracts;

use Abdeslam\DotEnv\Exceptions\InvalidResourceException;

interface ParserInterface
{
    /**
     * sets the resource of the current .env file to be parsed
     * @param resource $resource the .env file resource to parse
     * @return ParserInterface
     */
    public function setResource($resource): ParserInterface;

    /**
     * gets the resource of the current .env file
     * @return resource|null
     */
    public function getResource();

    /**
     * reads, parses and applies filters the content of a .env file
     *
     * @param array $oldItems the previously parsed items
     * @param array $filters list of filters to filter .env variables
     * @return array the .env content as key => value pairs
     * @throws InvalidResourceException
     */
    public function parse(array $oldItems, array $filters = []): array;
}