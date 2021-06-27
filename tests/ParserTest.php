<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

 namespace Tests;
 
use Abdeslam\DotEnv\Parser;
use PHPUnit\Framework\TestCase;
use Abdeslam\DotEnv\Filters\TrimQuotesFilter;
use Abdeslam\DotEnv\Filters\BooleanValueFilter;
use Abdeslam\DotEnv\Exceptions\InvalidEnvFileException;


 class ParserTest extends TestCase
 {
     /**
      * @test
      */
     public function parserResource()
     {
         $parser = new Parser();
         $file = __DIR__ . '/.env';
         $resource = fopen($file, 'r');
         $parser->setResource($resource);
         $this->assertIsResource($parser->getResource());
         $this->assertFileIsReadable($file);
         fclose($resource);

         $this->expectException(InvalidEnvFileException::class);
         $parser->setResource('invalid_resource');
     }

     /**
      * @test
      */
     public function parserParse()
     {
         $parser = new Parser();
         $resource = fopen(__DIR__ . '/.env', 'r');
         $parsedItems = $parser->setResource($resource)->parse([]);
         $expectedItems = [
            'username' => 'abdeslam',
            '"environment"' => 'dev',
            'debug' => "'true'",
            'verbose' => 'FALSE'
        ];
         $this->assertSame($expectedItems, $parsedItems);
         // with filters
         rewind($resource);
         $parsedItems = $parser->parse([], [
            TrimQuotesFilter::class,
            BooleanValueFilter::class
        ]);
         $expectedItems = [
            'username' => 'abdeslam',
            'environment' => 'dev',
            'debug' => true,
            'verbose' => false
        ];
         $this->assertSame($expectedItems, $parsedItems);
         fclose($resource);
     }
 }
