<?php
namespace Chalcedonyt\RedisTagger;

/**
 *
 */
abstract class Tagger implements TaggerInterface
{

    /**
     * @var Array $tags The keys supported by the key value tagger.
     * Can be either a plain string, a {tag}, or a {tag} with a Closure to be resolved.
     * E.g. ['campaign_performance', '{replace_string}', '{campaign} => function(Campaign $campaign){ return $campaign -> id }'];
     */
    protected $tags = [];
    /**
     * @var Array $tagValues The values to be replaced into tag keys.
     * E.g. with the $tags example above, with $campaign -> id of 1, a $tagValues of ['replace_string' => 'Hello', 'campaign' => $campaign ]
     * Would result in a key of 'campaign_performance:Hello:1'
     */
    protected $tagValues = [];

    const TAG_REGEX = '/{(.+)}/';
    const DELIMITER = ':';


    /**
     * Sets the values of the $tagValues array.
     */
    public function __set($property, $value){
        if( in_array( '{'.$property.'}', array_keys( $this -> getTags())) )
            $this -> tagValues[$property] = $value;
        else{
            throw new \InvalidArgumentException('Could not find a parameter tag for {'.$property.'}');
        }
    }

    /**
     * Derives the final Redis key from the $tagValues.
     * Every entry in $tags must have a corresponding entry in $tagValues.
     * Example:
     * Given a $tags of ['campaign_performance', '{team_id}','{campaign_id}']
     * And $tagValues of ['team_id' => 123, 'campaign_id' => 1234]
     * The result would be 'campaign_performance:123:1234'
     *
     * @return String The Redis Key
     */
    public function getKey()
    {
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

                if( !in_array( $plain_key, array_keys($this -> tagValues)))
                {
                    throw new \InvalidArgumentException('The key '.$key.' has no corresponding value');
                }

                /**
                 * if the tagValues[$plain_key] is not an object (i.e. not a Closure), just add it to the values.
                 */
                $tag_value = $this -> tagValues[$plain_key];

                if( !is_object($tags[$key]) ){
                    $values[] = $this -> tagValues[$plain_key];
                }
                /**
                 * if the tagValues[$plain_key] is a Closure, add the derived value to the values.
                 */
                else if( get_class($tags[$key] ) == \Closure::class ){
                    $closure = $tags[$key];
                    //resolve the closure with the $tag_value.
                    $values[]= $closure($tag_value);
                }
                /**
                 * No other types are supported yet.
                 */
                else{
                    throw new \InvalidArgumentException('The value '.$tag_key.' for '.$plain_key.' should be either a primitive or a Closure.');
                }
            }
            //If the key is not a tag template, just append it.
            else{
                $values[]=$key;
            }
        }
        return implode(static::DELIMITER, $values);
    }

    /**
     * Get all the tags, filtered by getPlainTags
     * @return Array of Strings
     */
    public function getPlainTags(){
        $tags = array_keys( $this -> getTags() );

        return array_map( function($tag){
            return $this -> getPlainTag( $tag );
        }, $tags );
    }

    /**
     * Because $tags can be either a keyed value or a plain value,
     * e.g. ['{value1}', '{value2}' => \Closure ]
     * Would be represented as [0 => '{value1}', '{value2}' => \Closure];
     * This is awkward to parse, so translate this into ['{value1}' => null, '{value2}' => \Closure];
     * @return Array $key_values
     */
    protected function getTags(){
        $key_values = [];
        foreach( $this -> tags as $index_or_key => $key_or_closure ){
            if(!is_object($key_or_closure)){
                $key_values[$key_or_closure] = null;
            } else if ( get_class($key_or_closure) == \Closure::class ){
                $key_values[$index_or_key] = $key_or_closure;
            } else {
                throw new \InvalidArgumentException('The key '.$key_or_closure.' should be either a String or a Closure.');
            }
        }
        return $key_values;
    }

    /**
     * @todo should use $tagRegex
     * Checks if the key is a string value or a tag template to be replaced.
     * E.g. {campaignid} would return true while "campaign_performance" would return false.
     */
    protected function isTagTemplate($key)
    {
        if( strpos($key, '{') !== false || strpos( $key, '}') !== false ){
            return true;
        }
        return false;
    }

    /**
     * Converts {campaign_id} to campaign_id
     * @param String the template key to convert
     * @return String the value
     */
    protected function getPlainTag($key)
    {
        $regex = static::TAG_REGEX;
        if( preg_match( $regex, $key, $result ) ){
            return $result[1];
        } else{
            return false;
        }
    }
}


?>
