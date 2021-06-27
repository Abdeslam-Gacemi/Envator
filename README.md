# Envator package
**[Envator](https://github.com/Abdeslam-Gacemi/Envator.git)** is a **.env** file loader that supports loading multiple files, caching (using psr-16 simple-cache interface) and using filters that unable type casting of booleans, numerics and variables inside values and more ... 

## Requirements
- PHP 8.0 and above.

## Installation

### 1. Using Composer
You can install the library via [Composer](https://getcomposer.org/).

```
php composer.phar require Abdeslam/Envator
```

or

```
composer require Abdeslam/Envator
```

### 2. Manually

If you're not using Composer, you can also clone `Abdeslam/Envator` repository into your directory:

```
git clone https://github.com/Abdeslam-Gacemi/Envator.git
```

However, using Composer is recommended as you can easily keep the library up-to-date.

## Usage
#
### 1. using the class Envator directly

let's assume that's your `.env` file

``` .env
APP_NAME=my_awesome_application
```

in your `php` code :

``` php
<?php

use Abdeslam\Envator\Envator;

require '/path/to/autoload.php';

$envator = new Envator();
$envator->load('/path/to/.env');
$envator->populate();

echo getenv('APP_NAME'); // output: 'my_awesome_application'
echo $_ENV['APP_NAME']; // output: 'my_awesome_application'
echo $_SERVER['APP_NAME']; // output: 'my_awesome_application'
```

loading multiple .env files

``` php
<?php

use Abdeslam\Envator\Envator;

require '/path/to/autoload.php';

$envator = new Envator();
// loading multiple .env files
$envator->load('/path/to/.env', '/path/to/another/.env');
$envator->populate();
```

### 2. using the factory

``` php
<?php

use Abdeslam\Envator\EnvatorFactory;

require '/path/to/autoload.php';

$envator = new EnvatorFactory::create([
    '/path/to/.env'
]);
$envator->populate();

echo getenv('APP_NAME'); // output: 'my_awesome_application'
echo $_ENV['APP_NAME']; // output: 'my_awesome_application'
echo $_SERVER['APP_NAME']; // output: 'my_awesome_application'
```

### 3. adding filters
filters allow operating on the the keys and values after the parsing of the `.env` file. Envator package provide 5 filters and you can add custom ones.

* `TrimQuotesFilter::class` : trims the quotes of the string keys and values.
* `BooleanValuesFilter::class` : casts 'true' and 'false' strings to booleans (case insensitive).
* `NumericValueFilter::class` : casts numeric string values to integers and floats. 
* `EmptyStringToNullFilter::class` : casts empty strings '' to NULL.
* `VariableFilter::class` : replaces variables inside values (variable must defined before using it inside values). __see example below__

let's assume that is your .env file :

``` .env
APP_NAME=my_awesome_application
ENVIRONMENT="development"
DEBUG=true
VERBOSITY_LEVEL=2

DATABASE_USER=user
DATABASE_PASSWORD=${DATABASE_USER}1234

EMPTY=
EMPTY2
```

in your `php` code :

``` php
<?php

use Abdeslam\Envator\Envator;
use Abdeslam\Envator\Filters\TrimQuotesFilter;
use Abdeslam\Envator\Filters\BooleanValueFilter;
use Abdeslam\Envator\Filters\NumericValueFilter;
use Abdeslam\Envator\Filters\VariableFilter;
use Abdeslam\Envator\Filters\EmptyStringToNullFilter;

require '/path/to/autoload.php';

$envator = new Envator();
$envator->addFilters([
    TrimQuotesFilter::class,
    BooleanValueFilter::class,
    NumericValueFilter::class,
    VariableFilter::class,
    EmptyStringToNullFilter::class
]);
$envator->load('/path/to/.env');
$envator->populate();

echo getenv('ENVIRONMENT'); // output: 'development' instead of '"development"'

echo $_ENV['DEBUG']; // output: true (boolean) instead of 'true' (string)

echo $_ENV['VERBOSITY_LEVEL']; // output: 2 (integer) instead of '2' (string)

echo $_SERVER['DATABASE_PASSWORD']; // output: 'user1234'

echo getenv('DEBUG'); // output: '1' 
// getenv() does not support booleans and NULL
echo getenv('EMPTY'); // output: '' 
// getenv() does not support booleans and NULL

echo $_ENV['EMPTY']; // output: NULL

echo $_SERVER['EMPTY2']; // output: NULL
```
> **CAVEATS :**
> * using getenv() function : it does not support booleans (TRUE and FALSE) and NULL, they get type casted to string : TRUE => '1', FALSE => '', NULL => ''.

### 4. filters using the factory
the factory loads the default filters 5 automatically, to change this behavior :
```php
<?php

use Abdeslam\Envator\EnvatorFactory;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(
    ['/path/to/.env'],
    [] // no filter will be loaded
);

```
or
```php
<?php

use Abdeslam\Envator\EnvatorFactory;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(
    ['/path/to/.env'],
    null // (default value) the default 5 filters will be loaded
);

```
or 
```php
<?php

use Abdeslam\Envator\EnvatorFactory;
use Abdeslam\Envator\Filters\BooleanValueFilter;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(
    ['/path/to/.env'],
    // loading needed filters only
    [BooleanValueFilter::class]
);

```

### 5. custom filters

A filter must be a class that implements `Abdeslam\Envator\Contracts\FilterInterface` :
 ```php
<?php

use Abdeslam\Envator\Contracts\FilterInterface;

class MyCustomFilter implements FilterInterface
{
    /**
     * @inheritDoc
     */
    public static function filter(array $oldItems, string $key, $value): array
    {
        $prefix = 'MY_PREFIX_';
        // the key exists in the items parsed previously
        // so it will be overwritten
        // we add a prefix to the key
        if (array_key_exists($key, $oldItems)) {
            $key = $prefix . $key;
        }
        return ['key' => $key, 'value' => $value];
    }
}

 ```
if we take this .env variables for example :
 ```
 USERNAME=user
 USERNAME=admin
 ```

```php
<?php

use Abdeslam\Envator\EnvatorFactory;
use MyCustomFilter;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(
    ['/path/to/.env'],
    // loading needed filters
    [MyCustomFilter::class]
);
$envator->populate();

echo getenv('USERNAME'); // output : 'user'
echo getenv('MY_PREFIX_USERNAME'); // output : 'admin'

```

### 6. options
when population the variables to the environment, you can specify what way the variables are populated by passing an array as an argument to `Envator::populate()` method :
```php
<?php

use Abdeslam\Envator\EnvatorFactory;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(['/path/to/.env']);
// populate the variables to the super global $_ENV only
$envator->populate([
    Envator::GLOBAL_ENV => true,
    Envator::PUT_ENV => false, // populate using putenv() function (risky)
    Envator::APACHE => false, // populates using apache_setenv() (for apache environment)
    Envator::SERVER => false // populates to the super global $_SERVER
]);

/**
 * the default configuration :
 * [
 *  Envator::GLOBAL_ENV => true,
 *  Envator::PUT_ENV => true,
 *  Envator::APACHE => false,
 *  Envator::SERVER => true,
 * ]
*/
```

### 7. caching
**Envator** support any implementation of psr-16 (simple-cache), and provides a default one :

```php
<?php

use Abdeslam\Envator\Envator;

require '/path/to/autoload.php';
// instantiating the cache manager providing the directory to use for caching
$cacheManager = new CacheManager(__DIR__);
$envator = new Envator();
// setting a cache manager enables the cache automatically
$envator->setCacheManager($cacheManager);
$envator->load('/path/to/.env')->populate();
```
or
```php
<?php

use Abdeslam\Envator\EnvatorFactory;

require '/path/to/autoload.php';

$envator = EnvatorFactory::create(
    ['/path/to/.env'],
    null,
    __DIR__ // cache directory
);
$envator->populate();
```


## Customization

* Creating a custom .env files Resolver and Parser classes, they must implement `Abdeslam\Envator\ResolverInterface` and `Abdeslam\Envator\ParserInterface` .
  
```php
<?php

use Abdeslam\Envator\Envator;

require '/path/to/autoload.php';

$resolver = new MyCustomResolver();
$parser = new MyCustomParser();
$envator = new Envator($resolver, $parser);
```

> Made with love :heart: