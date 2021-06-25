<?php

namespace Abdeslam\DotEnv\Contracts;

interface ResolverInterface
{
    public function __construct(array $filepaths);
    public function resolve();
}