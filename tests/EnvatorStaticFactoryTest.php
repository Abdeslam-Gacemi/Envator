<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

use PHPUnit\Framework\TestCase;
use Abdeslam\Envator\EnvatorFactory;

class EnvatorStaticFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function envatorStaticFactoryCreate()
    {
        EnvatorFactory::create([
            __DIR__ . '/.env'
        ])->populate();
        $this->assertSame('abdeslam', $_ENV['username']);
        $this->assertSame('abdeslam', $_SERVER['username']);
        $this->assertSame('abdeslam', getenv('username'));
        $this->assertSame(true, $_ENV['debug']);
        $this->assertSame(true, $_SERVER['debug']);
        $this->assertSame('1', getenv('debug'));
        $this->assertSame(false, $_ENV['verbose']);
        $this->assertSame(false, $_SERVER['verbose']);
        $this->assertSame('', getenv('verbose'));
    }

    /**
     * @test
     */
    public function envatorStaticFactoryCreateWithCache()
    {
        $envator = EnvatorFactory::create(
            [__DIR__ . '/.env'],
            null,
            __DIR__
        )->populate();
        $cache = $envator->getCacheManager()->get(__DIR__ . '/.env');
        $this->assertSame($envator->all(), $cache);
        unlink(__DIR__ . '/.env.cache.json');
    }
}
