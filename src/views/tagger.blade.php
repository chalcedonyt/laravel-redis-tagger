<?= '<?php' ?>

namespace {{$namespace}};

use Chalcedonyt\RedisTagger\Tagger;
use Chalcedonyt\RedisTagger\TaggerInterface;

class {{$classname}} extends Tagger
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
