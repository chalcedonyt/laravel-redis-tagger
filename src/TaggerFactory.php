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
     * @param String $class The classname
     * @param Array $key_values The values for the tag keys.
     * @return an instance of $class with the initialized values
     */
    public static function create($class_path, $key_values = []){
        if( strpos($class_path, 'App') === 0){
            $path = $class_path;
        }
        else $path = Config::get('redis_tagger.namespace').'\\'.$class_path;

        $class = new \ReflectionClass($path);
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
