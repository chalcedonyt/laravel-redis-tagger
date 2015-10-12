<?php
namespace Chalcedonyt\RedisTagger\KeyValue;

/**
 *
 */
use Config;

class KeyValueFactory
{
    /**
     * @todo this should be in a config file
     */
    const BASE_PATH = 'KeyValue' ;

    /**
     * Creates a new instance of a Redis key value tag and initializes it
     * @param String $class The classname relative to BASE_PATH
     * @param Array $key_values The values for the signature keys.
     * @param mixed $value The value to set
     * @return an instance of $class with the initialized values
     */
    public static function create($class, $key_values){
        $base_path = Config::get('redis_tagger.namespace').'\\'.static::BASE_PATH;

        $class = new \ReflectionClass($base_path.'\\'.$class);
        $key_value_tagger = $class -> newInstance();

        //Uses the magic __set on KeyValue
        foreach( $key_values as $key => $value )
        {
            $key_value_tagger -> $key = $value;
        }
        return $key_value_tagger;
    }
}


?>
