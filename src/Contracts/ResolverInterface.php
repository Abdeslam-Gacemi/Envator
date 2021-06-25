<?php

namespace Abdeslam\DotEnv\Contracts;

interface ResolverInterface
{
    public function setFilepaths(array $filepaths): ResolverInterface;
    public function getFilepaths(): array;
    public function resolve();
}