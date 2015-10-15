<?php
namespace Chalcedonyt\RedisTagger;

use Log;
use Redis;

class TaggerProxy {
    /**
     * The Redis key generated from the arguments
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @return String The key
     */
    public static function getKey( $class, $key_args )
    {
        $instance = TaggerFactory::create( $class, $key_args );
        return $instance -> getKey();
    }

    /**
     * Given a Tagger with the class $class, search for the value of the tag named $tag_name (without {}) if available.
     * E.g. given a KeyValueTagger with the tags ['somelabel','{campaign_id}'] and the generated key somelabel:123
     * calling valueOfTagInKey('myclass', 'campaign_id') would return 123.
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @param mixed $value the value to set
     * @return mixed $value the value of Redis::set
     */
    public static function valueOfTagInKey( $class, $key, $tag_name){
        $key_tags = explode( Tagger::DELIMITER, $key);
        $instance = TaggerFactory::create($class);
        $class_tags = $instance -> getPlainTags();

        if( in_array($tag_name, $class_tags)){
            $index = array_search($tag_name, $class_tags);
            return $key_tags[$index];
        }
        return false;
    }

    /**
     * Wrapper for Redis::set Sets a value into the cache
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @param mixed $value the value to set
     * @return mixed $value the value of Redis::set
     */
    public static function set($class, $key_args, $value){
        $key = static::getKey( $class, $key_args );
        Log::info('Storing into '.$key, ['value' => $value]);
        return Redis::set($key, $value);
    }

    /**
     * Wrapper for Redis::get Gets a value from the cache
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @return mixed The result of Redis::get
     */
    public function get($class, $key_args){
        $key = static::getKey( $class, $key_args );
        return Redis::get($key);
    }

    /**
     * Wrapper for Redis::keys
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @return Array the matched keys
     */
    public static function keys($class, $key_args)
    {
        $search_string = static::getSearchKey($class, $key_args);
        return Redis::keys( $search_string );
    }

    /**
     * Returns a key with wildcards where applicable
     * @param String $class the class name of the tagger, relative to KeyValueFactory::BASE_PATH
     * @param Array $key_args The keys to be replaced into the tagger.
     * @return String the derived key
     */
    public static function getSearchKey($class, $key_args )
    {
        $instance = TaggerFactory::create( $class, $key_args );
        return $instance -> getKeysForSearch();
    }

}
?>
