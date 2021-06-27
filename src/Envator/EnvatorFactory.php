<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\Envator;

use Abdeslam\Envator\Envator;
use Abdeslam\Envator\CacheManager;
use Abdeslam\Envator\Filters\VariableFilter;
use Abdeslam\Envator\Filters\TrimQuotesFilter;
use Abdeslam\Envator\Filters\BooleanValueFilter;
use Abdeslam\Envator\Filters\NumericValueFilter;
use Abdeslam\Envator\Filters\EmptyStringToNullFilter;
use Abdeslam\Envator\Exceptions\InvalidEnvFileException;

class EnvatorFactory
{
    protected static $defaultFilters = [
        TrimQuotesFilter::class,
        EmptyStringToNullFilter::class,
        BooleanValueFilter::class,
        NumericValueFilter::class,
        VariableFilter::class,
    ];
    /**
     * static factory method for Envator::class
     *
     * @param array $filepaths an array of filepaths of .env files
     * @param array $filters an array of FQCNs of implementations of FilterInterface::class
     * @param array $options an array of options for the populate() method:
     * - Envator::GLOBAL_ENV => bool : populate to super global $_ENV
     * - Envator::PUT_ENV => bool : populate with the function putenv()
     * - Envator::APACHE => bool : populate with the function apache_setenv()
     * - Envator::SERVER => bool : populate to super global $_SERVER
     * @var string|null $cacheDir the directory to use for caching (setting this parameter activates the cache automatically)
     * @return Envator
     */
    public static function create(array $filepaths, ?array $filters = null, ?string $cacheDir = null): Envator
    {
        if (empty($filepaths)) {
            throw new InvalidEnvFileException("The array of env files paths must contain at least one env file path");
        }
        if ($filters === null) {
            $filters = self::$defaultFilters;
        }
        $dotEnv = new Envator();
        if ($cacheDir !== null) {
            $dotEnv->setCacheManager(new CacheManager($cacheDir));
        }
        $dotEnv->setFilters($filters)
                ->load(...$filepaths);
        return $dotEnv;
    }
}
