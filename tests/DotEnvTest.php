<?php

namespace Tests;

use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Parser;
use Abdeslam\DotEnv\Resolver;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Filters\AddPrefixToKeyFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;

class DotEnvTest extends TestCase
{
    const DEFAULT_ENV_FILE = __DIR__ . '/.env';
    
    /**
     * @test
     */
    public function dotEnvFilters()
    {
        $dotEnv = new DotEnv(new Resolver(), new Parser());
        $dotEnv->addFilter(TrimQuotesFilter::class);
        $dotEnv->addFilter(BooleanValueFilter::class);
        $this->assertTrue($dotEnv->hasFilter(TrimQuotesFilter::class));
        $this->assertTrue($dotEnv->hasFilter(BooleanValueFilter::class));
        $this->assertSame(
            [TrimQuotesFilter::class, BooleanValueFilter::class],
            $dotEnv->getFilters()
        );
    }

    /**
     * @test
     */
    public function dotEnvLoad()
    {
        $dotEnv = $this->getDefaultDotEnv();
        $this->assertSame('abdeslam', $dotEnv->get('username'));
        $this->assertSame('dev', $dotEnv->get('"environment"'));
        $this->assertSame("'true'", $dotEnv->get('debug'));

        // adding filters
        $dotEnv->reset();
        $dotEnv->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(AddPrefixToKeyFilter::class); // custom filter

        $dotEnv->load(self::DEFAULT_ENV_FILE);
        var_dump($dotEnv->all());
        $this->assertSame('abdeslam', $dotEnv->get('MY_PREFIX_username'));
        $this->assertSame('dev', $dotEnv->get('MY_PREFIX_environment'));
        $this->assertSame(true, $dotEnv->get('MY_PREFIX_debug'));
    }

    /**
     * @test
     */
    public function dotEnvGet()
    {
        $dotEnv = $this->getDefaultDotEnv();
        $this->assertSame('abdeslam', $dotEnv->get('username'));

        $this->assertSame('default', $dotEnv->get('non_existing_key', 'default'));

        $this->expectException(InvalidArgumentException::class);
        $dotEnv->get('non_existing_key');
    }
    
    /**
     * @test
     */
    public function dotEnvHas()
    {
        $dotEnv = new DotEnv(new Resolver(), new Parser());
        $this->assertFalse($dotEnv->has('username'));
        $dotEnv->load(self::DEFAULT_ENV_FILE);
        $this->assertTrue($dotEnv->has('username'));
    }

    /**
     * @test
     */
    public function dotEnvAll()
    {
        $dotEnv = new DotEnv(new Resolver(), new Parser());
        $this->assertEmpty($dotEnv->all());
        $dotEnv->load(self::DEFAULT_ENV_FILE);
        $items = [
            'username' => 'abdeslam',
            '"environment"' => 'dev',
            'debug' => "'true'"
        ];
        $this->assertSame($items, $dotEnv->all());
    }

    protected function getDefaultDotEnv(): DotEnv
    {
        $dotEnv = new DotEnv(new Resolver(), new Parser());
        $dotEnv->load(__DIR__ . '/.env');
        return $dotEnv;
    }
}