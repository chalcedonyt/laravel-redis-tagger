<?php
namespace KeyValue;

use Models\User;
use Chalcedonyt\RedisTagger\Tagger;
use Chalcedonyt\RedisTagger\TaggerInterface;
/**
 *
 */
class BaseUserTagger extends Tagger implements TaggerInterface
{

    public function __construct(){
        $this -> tags = [
            'user_post_data',
            '{user}' => function( User $user ){
                return $user -> id;
            },
            '{gender}' => function( User $user ){
                return $user -> gender;
            }
        ];
    }
}


?>
