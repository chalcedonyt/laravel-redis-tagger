<?php
namespace Chalcedonyt\RedisTagger\Facades;
use Illuminate\Support\Facades\Facade;

class RedisKVTagger extends Facade{
    protected static function getFacadeAccessor() {
        return 'RedisKVTagger';
    }
}
?>
