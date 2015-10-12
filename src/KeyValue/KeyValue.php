<?php
namespace Chalcedonyt\RedisTagger\KeyValue;

/**
 *
 */
abstract class KeyValue
{

    public function __construct(){

    }
    
    /**
     * @var Array $signatureKeys The keys supported by the key value tagger.
     * Can be either a plain string, a {signature}, or a {signature} with a Closure to be resolved.
     * E.g. ['campaign_performance', '{replace_string}', '{campaign} => function(Campaign $campaign){ return $campaign -> id }'];
     */
    protected $signatureKeys = [];
    /**
     * @var Array $signatureValues The values to be replaced into signature keys.
     * E.g. with the $signatureKeys example above, with $campaign -> id of 1, a $signatureValues of ['replace_string' => 'Hello', 'campaign' => $campaign ]
     * Would result in a key of 'campaign_performance:Hello:1'
     */
    protected $signatureValues = [];

    const SIGNATURE_REGEX = '/{(.+)}/';
    const DELIMITER = ':';
    /**
     * Sets the values of the $signatureValues array.
     */
    public function __set($property, $value){
        if( in_array( '{'.$property.'}', array_keys( $this -> getSignatureKeys())) )
            $this -> signatureValues[$property] = $value;
        else{
            throw new \InvalidArgumentException('Could not find a parameter signature for {'.$property.'}');
        }
    }

    /**
     * Derives the final key from the $signatureValues.
     * Every entry in $signatureKeys must have a corresponding entry in $signatureValues.
     * Example:
     * Given a $signatureKeys of ['campaign_performance', '{team_id}','{campaign_id}']
     * And $signatureValues of ['team_id' => 123, 'campaign_id' => 1234]
     * The result would be 'campaign_performance:123:1234'
     *
     * @return String The Redis Key
     */
    public function getKey()
    {
        $values = [];
        /**
         * Go through the declared keys.
         * The signature keys may have a type hint. E.g. ['campaign_performance', 'campaign']
         */
        $signature_keys = $this -> getSignatureKeys();
        foreach( $signature_keys as $key => $null_or_closure )
        {
            /**
             * If the key is a template (e.g. "{campaignid}" is a template, while "campaignid" is not), search for the corresponding key in signatureValues.
             */
            if( $this -> isTemplateKey($key)){
                //extract '{campaign_id}' from signature to 'campaign_id'
                $plain_key = $this -> getSignatureValue($key);

                if( !in_array( $plain_key, array_keys($this -> signatureValues)))
                {
                    throw new \InvalidArgumentException('The key '.$key.' has no corresponding value');
                }

                /**
                 * if the signatureValues[$plain_key] is not an object, just add it to the values.
                 */
                $signature_value = $this -> signatureValues[$plain_key];

                if( !is_object($signature_keys[$key]) ){
                    $values[] = $this -> signatureValues[$plain_key];
                }
                /**
                 * if the signatureValues[$plain_key] is a Closure, add the derived value to the values.
                 */
                else if( get_class($signature_keys[$key] ) == \Closure::class ){
                    $closure = $signature_keys[$key];
                    //resolve the closure with the $signature_value.
                    $values[]= $closure($signature_value);
                }
                /**
                 * No other types are supported yet.
                 */
                else{
                    throw new \InvalidArgumentException('The value '.$signature_key.' for '.$plain_key.' should be either a String or a Closure.');
                }
            }
            //If the key is not a signature template, just append it.
            else{
                $values[]=$key;
            }
        }
        return implode(static::DELIMITER, $values);
    }

    /**
     * Because $signatureKeys can be either a keyed value or a plain value,
     * e.g. ['{value1}', '{value2}' => \Closure ]
     * Would be represented as [0 => '{value1}', '{value2}' => \Closure];
     * This is awkward to parse, so translate this into ['{value1}' => null, '{value2}' => \Closure];
     * @return Array $key_values
     */
    private function getSignatureKeys(){
        $key_values = [];
        foreach( $this -> signatureKeys as $index_or_key => $key_or_closure ){
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
     * @todo should use $signatureRegex
     * Checks if the key is a string value or a signature key template to be replaced.
     * E.g. {campaignid} would return true while "campaign_performance" would return false.
     */
    private function isTemplateKey($key)
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
    private function getSignatureValue($key)
    {
        $regex = static::SIGNATURE_REGEX;
        if( preg_match( $regex, $key, $result ) ){
            return $result[1];
        } else{
            return false;
        }
    }
    /**
     * Checks that the key doesn't have {}, and wraps it in {}
     * @param String the key to convert
     * @return String the template key.
     */
    private function toSignatureKey($key)
    {
        if( strpos($key, '{') !== false || strpos( $key, '}') !== false ){
            throw new \InvalidArgumentException('The characters {} are not allowed in the Redis key');
        }
        return '{'.$key.'}';
    }
}


?>
