<?php
namespace Chalcedonyt\RedisTagger;

/**
 *
 */
interface TaggerInterface
{
    /**
     * Sets the values of the $tagValues array.
     */
    public function __set($property, $value);

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
    public function getKey();

    /**
     * Get all the tags, filtered by getPlainTags
     * @return Array of Strings
     */
    public function getPlainTags();


}


?>
