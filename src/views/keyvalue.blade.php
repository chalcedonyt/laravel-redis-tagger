<?= '<?php' ?>

namespace {{$namespace}};

use Chalcedonyt\RedisTagger\KeyValue\KeyValue;
use Chalcedonyt\RedisTagger\TaggerInterface;

class {{$classname}} extends KeyValue
{
    /**
    * label:{id}
     */
    public function __construct(){
        parent::__construct();
        $this -> tags = [
            'label',
            '{id}'
        ];
    }

}
?>
