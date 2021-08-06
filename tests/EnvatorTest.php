<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Tests;

use Abdeslam\Envator\Envator;
use PHPUnit\Framework\TestCase;
use Abdeslam\Envator\CacheManager;
use Tests\Filters\AddPrefixToKeyFilter;
use Abdeslam\Envator\Filters\TrimQuotesFilter;
use Abdeslam\Envator\Filters\BooleanValueFilter;
use Abdeslam\Envator\Filters\NumericValueFilter;
use Abdeslam\Envator\Filters\EmptyStringToNullFilter;
use Abdeslam\Envator\Exceptions\ItemNotFoundException;

class EnvatorTest extends TestCase
{
    /** @var string */
    const DEFAULT_ENV_FILE = __DIR__ . '/.env';

    /** @var Envator */
    protected $envator;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->envator = (new Envator())->load(self::DEFAULT_ENV_FILE);
    }

    /**
     * @test
     */
    public function envatorFilters()
    {
        $this->envator->addFilter(TrimQuotesFilter::class)
            ->addFilter(BooleanValueFilter::class);
        $this->assertTrue($this->envator->hasFilter(TrimQuotesFilter::class));
        $this->assertTrue($this->envator->hasFilter(BooleanValueFilter::class));
        $this->assertSame(
            [TrimQuotesFilter::class, BooleanValueFilter::class],
            $this->envator->getFilters()
        );
    }

    /**
     * @test
     */
    public function envatorLoad()
    {
        $this->assertSame('abdeslam', $this->envator->get('username'));
        $this->assertSame('dev', $this->envator->get('"environment"'));
        $this->assertSame("'true'", $this->envator->get('debug'));
        $this->assertSame("FALSE", $this->envator->get('verbose'));

        // adding filters
        $this->envator->reset();
        $this->envator->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(NumericValueFilter::class)
                ->addFilter(AddPrefixToKeyFilter::class); // custom filter

        $this->envator->load(self::DEFAULT_ENV_FILE);
        $this->assertSame('abdeslam', $this->envator->get('MY_PREFIX_username'));
        $this->assertSame('dev', $this->envator->get('MY_PREFIX_environment'));
        $this->assertSame(true, $this->envator->get('MY_PREFIX_debug'));
        $this->assertSame(false, $this->envator->get('MY_PREFIX_verbose'));
    }

    /**
     * @test
     */
    public function envatorLoadMultipleFiles()
    {
        $this->envator->reset()
                ->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(NumericValueFilter::class)
                ->addFilter('non_existent');
        $this->envator->load(self::DEFAULT_ENV_FILE, __DIR__ . '/another.env');
        $this->assertSame('abdeslam', $this->envator->get('username'));
        $this->assertSame('dev', $this->envator->get('environment'));
        $this->assertSame(true, $this->envator->get('debug'));
        $this->assertSame(false, $this->envator->get('verbose'));
        $this->assertSame('me@email.com', $this->envator->get('DEFAULT_EMAIL'));
        $this->assertSame('en', $this->envator->get('DEFAULT_LOCALIZATION'));
        $this->assertSame(null, $this->envator->get('DEFAULT_NULL_1'));
        $this->assertSame('', $this->envator->get('DEFAULT_NULL_2'));
    }
    
    /**
     * @test
     */
    public function envatorLoadWithCache()
    {
        $cacheManager = new CacheManager(__DIR__);
        $this->envator->reset()
                ->addFilter(TrimQuotesFilter::class)
                ->addFilter(BooleanValueFilter::class)
                ->addFilter(NumericValueFilter::class);
        $this->envator->setCacheManager($cacheManager);
        $this->envator->load(self::DEFAULT_ENV_FILE);
        $cache = $cacheManager->get(self::DEFAULT_ENV_FILE);
        $this->assertSame($this->envator->all(), $cache);
        unlink(__DIR__ . '/.env.cache.json');
    }

    /**
     * @test
     */
    public function envatorGet()
    {
        $this->assertSame('abdeslam', $this->envator->get('username'));

        $this->assertSame('default', $this->envator->get('non_existing_key', 'default'));

        $this->expectException(ItemNotFoundException::class);
        $this->envator->get('non_existing_key');
    }
    
    /**
     * @test
     */
    public function envatorHas()
    {
        $envator = new Envator();
        $this->assertFalse($envator->has('username'));
        $envator->load(self::DEFAULT_ENV_FILE);
        $this->assertTrue($envator->has('username'));
    }

    /**
     * @test
     */
    public function envatorAll()
    {
        $envator = new Envator();
        $this->assertEmpty($envator->all());
        $envator->load(self::DEFAULT_ENV_FILE);
        $items = [
            'username' => 'abdeslam',
            '"environment"' => 'dev',
            'debug' => "'true'",
            'verbose' => 'FALSE'
        ];
        $this->assertSame($items, $envator->all());
    }

    
    /**
     * @test
     */
    public function envatorPopulate()
    {
        $this->envator->reset();
        $this->envator->addFilter(TrimQuotesFilter::class)
                    ->addFilter(EmptyStringToNullFilter::class)
                    ->addFilter(BooleanValueFilter::class)
                    ->addFilter(NumericValueFilter::class);
        $this->envator->load(self::DEFAULT_ENV_FILE, __DIR__ . '/another.env')->populate();
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
