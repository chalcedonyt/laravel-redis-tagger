<?php
/**
 * Tests all exposed index and show endpoints
 */

use KeyValue\BaseUserTagger;
use Models\User;
use Chalcedonyt\RedisTagger\Facades\RedisKVTagger;
class KVTest extends Orchestra\Testbench\TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('redis_tagger.namespace','');
    }

    protected function getPackageProviders($app)
    {
        return ['Chalcedonyt\RedisTagger\Providers\RedisTaggerServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'RedisKVTagger' => 'Chalcedonyt\RedisTagger\Facades\RedisKVTagger'
        ];
    }

    public function testBasicUserKey(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => $user,
            'gender' => $user ];
        // RedisKVTagger::set('/KeyValue/BaseUserTagger', ['user' => $user], 'newvalue');
        $this -> assertEquals( 'user_post_data:123:2', RedisKVTagger::getKey('BaseUserTagger', $args) );
    }


    public function testThatAllParametersAreRequired(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = ['user' => $user];
        $this -> setExpectedException('InvalidArgumentException');
        RedisKVTagger::getKey('BaseUserTagger', $args);
    }

    public function testThatTagClosureValidatesType(){
        $user = new stdClass(); //not the expected User type.
        $user -> id = 123;
        $user -> gender = 2;

        $args = ['user' => $user, 'gender' => $user];
        $this -> setExpectedException('ErrorException');
        RedisKVTagger::getKey('BaseUserTagger', $args);
    }

    public function testExtendedTagger(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => $user,
            'gender' => $user ];
        $this -> assertEquals( 'user_post_data:123:2:posts', RedisKVTagger::getKey('UserPosts', $args) );
    }

    public function testKeySearch(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => '*',
            ];
        $this -> assertEquals( 'user_post_data:*:*:posts', RedisKVTagger::keys('UserPosts', $args) );
    }

    public function testWildCardKeySearch(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => '12?',
            ];
        $this -> assertEquals( 'user_post_data:12?:*:posts', RedisKVTagger::keys('UserPosts', $args) );
    }

    public function testGettingTagValue(){

        $key = 'user_post_data:123:2:posts';

        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $this -> assertEquals( '2', RedisKVTagger::valueOfTagInKey('BaseUserTagger', $key, 'gender') );
    }


}

?>
