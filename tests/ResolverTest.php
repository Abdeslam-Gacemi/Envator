<?php

/**
 * @author Abdeslam Gacemi <abdobling@gmail.com>
 */

namespace Tests;

use Abdeslam\Envator\Resolver;
use PHPUnit\Framework\TestCase;
use Abdeslam\Envator\Exceptions\InvalidEnvFileException;

 class ResolverTest extends TestCase
 {
     /**
      * @test
      */
     public function resolverResolve()
     {
         $resolver = new Resolver();
         $resolver->setFilepaths(__DIR__ . '/.env');
         $resolver->resolve();

         $resolver->setFilepaths('non_existing_file_path');
         $this->expectException(InvalidEnvFileException::class);
         $resolver->resolve();
     }

     /**
      * @test
      */
     public function resolverFilepaths()
     {
         $resolver = new Resolver();
         $this->assertEmpty($resolver->getFilepaths());
         $filepaths = [__DIR__ . '/.env', __DIR__ . '/.another.env'];
         $resolver->setFilepaths(...$filepaths);
         $this->assertSame($filepaths, $resolver->getFilepaths());
     }
 }
