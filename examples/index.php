<?php

use Abdeslam\DotEnv\DotEnv;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Filters\NumericValueFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\VariableFilter;

require __DIR__.'/../vendor/autoload.php';

$dotEnv = new DotEnv();
$dotEnv->addFilter(TrimQuotesFilter::class)
    ->addFilter(NumericValueFilter::class)
    ->addFilter(BooleanValueFilter::class)
    ->addFilter(VariableFilter::class);
$dotEnv->load(__dir__ . '/.env')->populate();