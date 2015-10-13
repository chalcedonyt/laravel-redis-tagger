<?php
namespace Chalcedonyt\RedisTagger;

/**
 *
 */
use Config;

class TaggerFactory
{
    /**
     * Creates a new instance of a RedisTagger\KeyValue and initializes it
     * @param String $base_path The path after the configure namespace path (e.g. KeyValue, or Set)
     * @param String $class The classname relative to the base_path
     * @param Array $key_values The values for the tag keys.
     * @return an instance of $class with the initialized values
     */
    public static function createTagger($base_path, $class, $key_values = []){
        $path = Config::get('redis_tagger.namespace').'\\'.$base_path;

        $class = new \ReflectionClass($path.'\\'.$class);
        $instance = $class -> newInstance();

        //Uses the magic __set on KeyValue
        foreach( $key_values as $key => $value )
        {
            $instance -> $key = $value;
        }
        return $instance;
    }
}


?>
