<?php
namespace KeyValue;

use Models\User;
use Chalcedonyt\RedisTagger\KeyValue\KeyValue;
/**
 *
 */
class BaseUserTagger extends KeyValue
{

    public function __construct(){
        $this -> signatureKeys = [
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
