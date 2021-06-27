<?php

/**
* @author Abdeslam Gacemi <abdobling@gmail.com>
*/

namespace Tests;

use PHPUnit\Framework\TestCase;
use Abdeslam\DotEnv\Filters\VariableFilter;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Filters\NumericValueFilter;
use Abdeslam\DotEnv\Filters\EmptyStringToNullFilter;

class FiltersTest extends TestCase
{
    /**
     * @test
     */
    public function trimQuotesFilter()
    {
        $filtered = TrimQuotesFilter::filter([], 'key', '"value"');
        $expected = ['key' => 'key', 'value' => 'value'];
        $this->assertSame($expected, $filtered);
    }
    
    /**
     * @test
     */
    public function booleanValueFilter()
    {
        $filtered = BooleanValueFilter::filter([], 'key', 'true');
        $expected = ['key' => 'key', 'value' => true];
        $this->assertSame($expected, $filtered);
    }
        
    /**
     * @test
     */
    public function numericValueFilter()
    {
        $filtered = NumericValueFilter::filter([], 'key', '2021');
        $expected = ['key' => 'key', 'value' => 2021];
        $this->assertSame($expected, $filtered);
        $filtered = NumericValueFilter::filter([], 'key', '10.02');
        $expected = ['key' => 'key', 'value' => 10.02];
        $this->assertSame($expected, $filtered);
    }

    /**
     * @test
     */
    public function emptyStringToNullFilter()
    {
        $filtered = EmptyStringToNullFilter::filter([], 'key', '');
        $expected = ['key' => 'key', 'value' => null];
        $this->assertSame($expected, $filtered);
    }
            
    /**
     * @test
     */
    public function variableFilter()
    {
        $oldItems = ['greeting' => 'Hello, world'];
        $filtered = VariableFilter::filter($oldItems, 'key', '${greeting} !');
        $expected = ['key' => 'key', 'value' => 'Hello, world !'];
        $this->assertSame($expected, $filtered);
    }
}
