<?php
spl_autoload_register(function ($class) {
    if( strpos($class, 'KeyValue\\') === 0 ){
        include __DIR__.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR,$class). '.php';
    }
    if( strpos($class, 'Models\\') === 0 ){
        include __DIR__.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR,$class). '.php';
    }
});
?>
