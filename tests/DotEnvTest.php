<?php

namespace Tests;

use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\Parser;
use Abdeslam\DotEnv\Resolver;
use PHPUnit\Framework\TestCase;

class DotEnvTest extends TestCase
{
    /**
     * @test
     */
    public function dotEnvInit()
    {
        $dotEnv = new DotEnv(new Resolver(), new Parser());
        $dotEnv->load(__DIR__ . '/.env');
        $dotEnv->populate();
    }
}