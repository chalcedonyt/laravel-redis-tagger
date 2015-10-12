<?= '<?php' ?>

namespace {{$namespace}};

use Chalcedonyt\RedisTagger\KeyValue\KeyValue;


class {{$classname}} extends KeyValue
{
    /**
    * label:{id}
     */
    public function __construct(){
        parent::__construct();
        $this -> signatureKeys = [
            'label',
            '{id}'
        ];
    }

}
?>
