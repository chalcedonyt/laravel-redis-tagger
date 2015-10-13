<?php
namespace Chalcedonyt\RedisTagger\KeyValue;

use Chalcedonyt\RedisTagger\Tagger;
/**
 *
 */
abstract class KeyValue extends Tagger
{

    public $shouldValidateKeys = true;

    public function __construct(){

    }

    /**
     * Derives the final Redis key from the $tagValues to be used in the Redis::keys command
     *
     * Every entry in $tags that doesn't have a corresponding entry in $tagValues is replaced with *
     * Any type-hinting error into a {tag} with a Closure will be suppressed.
     *
     * Example:
     * Given a $tags of ['campaign_performance', '{team_id}','{campaign_id}' => \Closure]
     * And $tagValues of ['campaign_id' => '1??']
     * The result would be 'campaign_performance:*:1??'
     *
     * @return String The Redis Key
     */
    public function getKeysForSearch(){
        $values = [];
        /**
         * Go through the declared keys.
         * The tag keys may have a type hint. E.g. ['campaign_performance', 'campaign']
         */
        $tags = $this -> getTags();
        foreach( $tags as $key => $null_or_closure )
        {
            /**
             * If the key is a template (e.g. "{campaignid}" is a template, while "campaignid" is not), search for the corresponding key in tagValues.
             */
            if( $this -> isTagTemplate($key)){
                //extract '{campaign_id}' from tag to 'campaign_id'
                $plain_key = $this -> getPlainTag($key);
                /**
                 * In search mode, replace any keys not found with '*'
                 */
                if( !in_array( $plain_key, array_keys($this -> tagValues)))
                {
                    $values[]= '*';
                    continue;
                }

                /**
                 * if the tagValues[$plain_key] is not an object, just add it to the values.
                 */
                $tag_value = $this -> tagValues[$plain_key];

                if( !is_object($tags[$key]) ){
                    $values[] = $this -> tagValues[$plain_key];
                }
                /**
                 * if the tagValues[$plain_key] is a Closure, try to add the derived value to the values, but if there is an exception, use the value verbatim.
                 */
                else if( get_class($tags[$key] ) == \Closure::class ){
                    $closure = $tags[$key];
                    //resolve the closure with the $tag_value.
                    try{
                        $values[]= $closure($tag_value);
                    } catch(\ErrorException $e)
                    {
                        $values[]=$tag_value;
                    }
                }
                /**
                 * Use the value verbatim
                 */
                else{
                    $values[]= $tag_value;
                }
            }
            //If the key is not a tag template, just append it.
            else{
                $values[]=$key;
            }
        }
        return implode(Tagger::DELIMITER, $values);
    }
}


?>
