<?php

namespace Abdeslam\DotEnv\Contracts;

interface CacheManagerInterface
{
    public function __construct(string $dir);
    public function has(string $key);
    public function get(string $key);
    public function set(string $key, array $value): bool;
}