<?php
namespace Chalcedonyt\RedisTagger\KeyValue;

use Chalcedonyt\RedisTagger\KeyValue\KeyValueFactory;
use Redis;
use Log;

/**
 * The proxy class that is called by the Facade. Initializes the correct key value tagger with the correct arguments.
 */
class KeyValueProxy
{
    /**
     * Sets a value into the cache
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @param mixed $value The result of Redis::set
     */
    public static function set($class, $key_args, $value){
        $key = static::getKey( $class, $key_args );
        Log::info('Storing into '.$key, ['value' => $value]);
        return Redis::set($key, $value);
    }

    /**
     * Gets a value from the cache
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @return mixed The result of Redis::get
     */
    public function get($class, $key_args){
        $key = static::getKey( $class, $key_args );
        return Redis::get($key);
    }

    /**
     * The key associated with the arguments
     */
    public static function getKey( $class, $key_args )
    {
        $class = KeyValueFactory::create( $class, $key_args );
        return $class -> getKey();
    }
}


?>
