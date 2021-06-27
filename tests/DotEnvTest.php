<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Tests;

use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\Parser;
use Abdeslam\DotEnv\Resolver;
use PHPUnit\Framework\TestCase;
use Tests\Filters\AddPrefixToKeyFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Filters\NumericValueFilter;
use Abdeslam\DotEnv\Filters\EmptyStringToNullFilter;
use Abdeslam\DotEnv\Exceptions\ItemNotFoundException;

class DotEnvTest extends TestCase
{
    /** @var string */
    const DEFAULT_ENV_FILE = __DIR__ . '/.env';

    /** @var DotEnv */
    protected $dotEnv;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->dotEnv = (new DotEnv())->load(self::DEFAULT_ENV_FILE);
    }

    /**
     * @test
     */
    public function dotEnvFilters()
    {
        $this->dotEnv->addFilter(TrimQuotesFilter::class);
        $this->dotEnv->addFilter(BooleanValueFilter::class);
        $this->assertTrue($this->dotEnv->hasFilter(TrimQuotesFilter::class));
        $this->assertTrue($this->dotEnv->hasFilter(BooleanValueFilter::class));
        $this->assertSame(
            [TrimQuotesFilter::class, BooleanValueFilter::class],
            $this->dotEnv->getFilters()
        );
    }

    /**
     * @test
     */
    public function dotEnvLoad()
    {
        $this->assertSame('abdeslam', $this->dotEnv->get('username'));
        $this->assertSame('dev', $this->dotEnv->get('"environment"'));
        $this->assertSame("'true'", $this->dotEnv->get('debug'));
        $this->assertSame("FALSE", $this->dotEnv->get('verbose'));

        // adding filters
        $this->dotEnv->reset();
        $this->dotEnv->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(NumericValueFilter::class)
                ->addFilter(AddPrefixToKeyFilter::class); // custom filter

        $this->dotEnv->load(self::DEFAULT_ENV_FILE);
        $this->assertSame('abdeslam', $this->dotEnv->get('MY_PREFIX_username'));
        $this->assertSame('dev', $this->dotEnv->get('MY_PREFIX_environment'));
        $this->assertSame(true, $this->dotEnv->get('MY_PREFIX_debug'));
        $this->assertSame(false, $this->dotEnv->get('MY_PREFIX_verbose'));
    }

    /**
     * @test
     */
    public function dotEnvLoadMultipleFiles()
    {
        $this->dotEnv->reset()
                ->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(NumericValueFilter::class);
        $this->dotEnv->load(self::DEFAULT_ENV_FILE, __DIR__ . '/another.env');
        $this->assertSame('abdeslam', $this->dotEnv->get('username'));
        $this->assertSame('dev', $this->dotEnv->get('environment'));
        $this->assertSame(true, $this->dotEnv->get('debug'));
        $this->assertSame(false, $this->dotEnv->get('verbose'));
        $this->assertSame('me@email.com', $this->dotEnv->get('DEFAULT_EMAIL'));
        $this->assertSame('en', $this->dotEnv->get('DEFAULT_LOCALIZATION'));
        $this->assertSame(null, $this->dotEnv->get('DEFAULT_NULL_1'));
        $this->assertSame('', $this->dotEnv->get('DEFAULT_NULL_2'));
    }

    /**
     * @test
     */
    public function dotEnvGet()
    {
        $this->assertSame('abdeslam', $this->dotEnv->get('username'));

        $this->assertSame('default', $this->dotEnv->get('non_existing_key', 'default'));

        $this->expectException(ItemNotFoundException::class);
        $this->dotEnv->get('non_existing_key');
    }
    
    /**
     * @test
     */
    public function dotEnvHas()
    {
        $this->dotEnv = new DotEnv(new Resolver(), new Parser());
        $this->assertFalse($this->dotEnv->has('username'));
        $this->dotEnv->load(self::DEFAULT_ENV_FILE);
        $this->assertTrue($this->dotEnv->has('username'));
    }

    /**
     * @test
     */
    public function dotEnvAll()
    {
        $this->dotEnv = new DotEnv(new Resolver(), new Parser());
        $this->assertEmpty($this->dotEnv->all());
        $this->dotEnv->load(self::DEFAULT_ENV_FILE);
        $items = [
            'username' => 'abdeslam',
            '"environment"' => 'dev',
            'debug' => "'true'",
            'verbose' => 'FALSE'
        ];
        $this->assertSame($items, $this->dotEnv->all());
    }

    
    /**
     * @test
     */
    public function dotEnvPopulate()
    {
        $this->dotEnv->reset();
        $this->dotEnv->addFilter(TrimQuotesFilter::class)
                    ->addFilter(EmptyStringToNullFilter::class)
                    ->addFilter(BooleanValueFilter::class)
                    ->addFilter(NumericValueFilter::class);
        $this->dotEnv->load(self::DEFAULT_ENV_FILE, __DIR__ . '/another.env')->populate();
        $this->assertSame('abdeslam', $_ENV['username']);
        $this->assertSame('abdeslam', getenv('username'));
        $this->assertSame('abdeslam', $_SERVER['username']);
        // getenv() translates NULL to an empty string automatically
        $this->assertSame('', getenv('DEFAULT_NULL_1'));
        $this->assertSame(null, $_ENV['DEFAULT_NULL_1']);
        $this->assertSame('', getenv('DEFAULT_NULL_2'));
        $this->assertSame(null, $_ENV['DEFAULT_NULL_2']);
    }
}
