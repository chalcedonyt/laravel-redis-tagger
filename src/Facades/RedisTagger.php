<?php
namespace Chalcedonyt\RedisTagger\Facades;
use Illuminate\Support\Facades\Facade;

class RedisTagger extends Facade{
    protected static function getFacadeAccessor() {
        return 'RedisTagger';
    }
}
?>
