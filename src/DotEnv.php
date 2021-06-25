<?php

namespace Abdeslam\DotEnv;

use Abdeslam\DotEnv\Parser;
use Abdeslam\DotEnv\Resolver;

class DotEnv
{
    /** @var Resolver */
    protected $resolver;
    
    /** @var Parser */
    protected $parser;
    
    /** @var array */
    protected $filepaths = [];

    /** @var array */
    protected $items = [];

    /** @var array */
    protected $options = [];

    public function __construct(Resolver $resolver, Parser $parser) {
        $this->resolver = $resolver;
        $this->parser = $parser;
    }

    public function load(string ...$filepaths)
    {
        // code
    }

    public function populate(array $options = [])
    {
        // code
    }

}
