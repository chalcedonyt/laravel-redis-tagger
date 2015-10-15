# chalcedonyt/laravel-redis-tagger

A helper Facade and functions to organize Redis tags, enabling easy type-hinting, built-in parameter validation, and placeholder replacement.

## Install

Via Composer (minimum stability must be set to dev)

``` bash
$ composer require chalcedonyt/laravel-redis-tagger
```

Include the Provider and Facade into app.php.

```php
Chalcedonyt\RedisTagger\Providers\RedisTaggerServiceProvider::class
```
```php
'RedisTagger' => Chalcedonyt\RedisTagger\Facades\RedisKVTagger::class
```
## Usage - GET/SET

``` php
php artisan redis_tagger:make UserPosts\\PostCount
```

The only thing you need to set is the `tags` value. You may insert either a plain string, a {tagtemplate}, or a {tagtemplate} with a \Closure that returns a value. (This allows type-hinting).

Any {tagtemplate} keys must be defined when called.

```php
class PostCount extends KeyValue
{
    public function __construct(){
        parent::__construct();
        $this -> tags = [
            'user_posts',
            '{type}',
            '{post_id}' => function( Post $post ){
                return $post -> id;
            },
            'count'
        ];
    }
}
```

For key-value operations, you may then call ::set or ::get on the `RedisTagger` Facade:

```php
$post = new Post();
$post -> id = 123
$args = ['type' => 'article', 'post_id' => $post ];
RedisTagger::set('UserPosts\\PostCount', $args, 1000); //sets the key "user_posts:article:123:count" to 1000.
```

Likewise, you can retrieve the value with
```php
RedisTagger::get('UserPosts\\PostCount', $args);
```
You may return only a key (e.g. for use with sets)
```php
RedisTagger::getKey('UserPosts\\PostCount', $args); //returns "user_posts:article:123:count"
```

It is also possible to extend any taggers you create by adding to the parent's $tags variable.

```php
class PostCountToday extends PostCount
{
    public function __construct(){
        parent::__construct();
        $this -> tags[]= 'today';
    }    
}
```
```php
class PostCountYesterday extends PostCount
{
    public function __construct(){
        parent::__construct();
        $this -> tags[]= 'yesterday';
    }    
}
```

```
RedisTagger::set('UserPosts\PostCountToday', $args, 1000); //sets the key "user_posts:article:123:count:today" to 1000.
RedisTagger::set('UserPosts\PostCountYesterday', $args, 1000); //sets the key "user_posts:article:123:count:yesterday" to 1000.
```

## Usage - KEYS

RedisTagger also wraps the `::keys` function of Redis. When calling `keys`, no validation is done on the arguments. Any missing {tagtemplates} will be cast to `*`:

```php
$args = ['type' => 'a??'];
RedisTagger::keys('UserPosts\PostCount', $args); //returns "user_posts:a??:*:count"
```

## Usage - Extracting tag values

You may extract the value of a {tag} from a key by using the ::valueOfTagInKey function.
```php
$key = "user_posts:article:123:count:yesterday";

RedisKVTagger::valueOfTagInKey('KeyValue\UserPosts\PostCount', $key, 'post_id'); //returns "123"
//or
RedisTagger::valueOfTagInKey('KeyValue\UserPosts\PostCount', $key, 'post_id'); //returns "123"
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.



## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
