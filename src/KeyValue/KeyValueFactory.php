<?php
namespace Chalcedonyt\RedisTagger\KeyValue;

use Chalcedonyt\RedisTagger\TaggerFactory;

class KeyValueFactory extends TaggerFactory
{
    /**
     * @todo this should be in a config file
     */
    const BASE_PATH = 'KeyValue';

    /**
     * Creates a new instance of a RedisTagger\KeyValue and initializes it
     * @param String $class The classname relative to BASE_PATH
     * @param Array $key_values The values for the tag keys.
     * @return an instance of $class with the initialized values
     */
    public static function create($class, $key_values = []){
        return TaggerFactory::createTagger(static::BASE_PATH, $class, $key_values );
    }
}


?>
