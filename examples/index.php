<?php

use Abdeslam\DotEnv\CacheManager;
use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Filters\NumericValueFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\VariableFilter;

require __DIR__.'/../vendor/autoload.php';

$cache = new CacheManager(__DIR__ . '/cache');
$dotEnv = new DotEnv();
$dotEnv->setCacheManager($cache);
$dotEnv->addFilter(TrimQuotesFilter::class)
    ->addFilter(NumericValueFilter::class)
    ->addFilter(BooleanValueFilter::class)
    ->addFilter(VariableFilter::class);
$dotEnv->load(__dir__ . '/.env', __DIR__ . '/.another.env')->populate();