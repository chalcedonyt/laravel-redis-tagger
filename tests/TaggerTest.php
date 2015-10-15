<?php
/**
 * Tests all exposed index and show endpoints
 */

use KeyValue\BaseUserTagger;
use Models\User;
use Chalcedonyt\RedisTagger\Facades\RedisTagger;
class TaggerTest extends Orchestra\Testbench\TestCase
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
        $this -> assertEquals( 'user_post_data:123:2', RedisTagger::getKey('KeyValue\\BaseUserTagger', $args) );
    }


    public function testThatAllParametersAreRequired(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = ['user' => $user];
        $this -> setExpectedException('InvalidArgumentException');
        RedisTagger::getKey('KeyValue\\BaseUserTagger', $args);
    }

    public function testThatTagClosureValidatesType(){
        $user = new stdClass(); //not the expected User type.
        $user -> id = 123;
        $user -> gender = 2;

        $args = ['user' => $user, 'gender' => $user];
        $this -> setExpectedException('ErrorException');
        RedisTagger::getKey('KeyValue\\BaseUserTagger', $args);
    }

    public function testExtendedTagger(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => $user,
            'gender' => $user ];
        $this -> assertEquals( 'user_post_data:123:2:posts', RedisTagger::getKey('KeyValue\\UserPosts', $args) );
    }

    public function testKeySearch(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $args = [
            'user' => '*',
            ];
        $this -> assertEquals( 'user_post_data:*:*:posts', RedisTagger::getSearchKey('KeyValue\\UserPosts', $args) );
    }

    public function testWildCardKeySearch(){
        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;
        $args = ['user' => $user, 'gender' => $user ];
        RedisTagger::set('KeyValue\\UserPosts', $args, 123);

        $wildcard_args = [
            'user' => '12?',
            ];

        $search_key = RedisTagger::getSearchKey('KeyValue\\UserPosts', $wildcard_args);
        $this -> assertEquals( 'user_post_data:12?:*:posts', $search_key );
        $this -> assertEquals( 1, count(RedisTagger::keys('KeyValue\\UserPosts', $wildcard_args )));
    }

    public function testGettingTagValue(){

        $key = 'user_post_data:123:2:posts';

        $user = new User();
        $user -> id = 123;
        $user -> gender = 2;

        $this -> assertEquals( '2', RedisTagger::valueOfTagInKey('KeyValue\\BaseUserTagger', $key, 'gender') );
    }


}

?>
