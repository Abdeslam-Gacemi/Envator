<?php

namespace Abdeslam\DotEnv\Contracts;

interface ParserInterface
{
    /**
     * @param string $content the .env content to parse
     */
    public function __construct(string $content);

    /**
     * reads and parses the content of a .env file
     *
     * @param array $filters list of filters to filter .env variables
     * @return array the .env content as key => value pairs
     */
    public function parse(array $filters): array;
}