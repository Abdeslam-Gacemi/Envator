<?php

namespace Abdeslam\DotEnv\Contracts;

interface ParserInterface
{
    /**
     * @param resource $resource the .env file resource to parse
     * @return ParserInterface
     */
    public function setResource($resource): ParserInterface;

    /**
     * @return resource|null
     */
    public function getResource();

    /**
     * reads and parses the content of a .env file
     *
     * @param array $filters list of filters to filter .env variables
     * @return array the .env content as key => value pairs
     */
    public function parse(array $oldItems, array $filters = []): array;
}