<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\CacheManager;
use Abdeslam\DotEnv\Filters\VariableFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Filters\NumericValueFilter;
use Abdeslam\DotEnv\Filters\EmptyStringToNullFilter;
use Abdeslam\DotEnv\Exceptions\InvalidEnvFileException;

class DotEnvFactory
{
    protected static $defaultFilters = [
        TrimQuotesFilter::class,
        EmptyStringToNullFilter::class,
        BooleanValueFilter::class,
        NumericValueFilter::class,
        VariableFilter::class,
    ];
    /**
     * static factory method for DotEnv::class
     *
     * @param array $filepaths an array of filepaths of .env files
     * @param array $filters an array of FQCNs of implementations of FilterInterface::class
     * @param array $options an array of options for the populate() method:
     * - DotEnv::GLOBAL_ENV => bool : populate to super global $_ENV
     * - DotEnv::PUT_ENV => bool : populate with the function putenv()
     * - DotEnv::APACHE => bool : populate with the function apache_setenv()
     * - DotEnv::SERVER => bool : populate to super global $_SERVER
     * @var string|null $cacheDir the directory to use for caching (setting this parameter activates the cache automatically)
     * @return DotEnv
     */
    public static function create(array $filepaths, ?array $filters = null, array $options = [], ?string $cacheDir = null): DotEnv
    {
        if (empty($filepaths)) {
            throw new InvalidEnvFileException("The array of env files paths must contain at least one env file path");
        }
        if ($filters === null) {
            $filters = self::$defaultFilters;
        }
        $dotEnv = new DotEnv();
        if ($cacheDir !== null)
        {
            $dotEnv->setCacheManager(new CacheManager($cacheDir));
        }
        $dotEnv->setFilters($filters)
                ->load(...$filepaths);
        $dotEnv->populate($options);
        return $dotEnv;
    }
}
