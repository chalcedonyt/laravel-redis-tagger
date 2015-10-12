<?php
namespace Chalcedonyt\RedisTagger\Providers;

use Illuminate\Support\ServiceProvider;
use Chalcedonyt\RedisTagger\Commands\KVGeneratorCommand;

class RedisTaggerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $source_config = __DIR__ . '/../config/redis_tagger.php';
        $this->publishes([
            $source_config => base_path('config/redis_tagger.php')
        ]);

        $this->loadViewsFrom(__DIR__ . '/../views', 'redis_tagger');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this -> app -> bind('RedisKVTagger', function(){
            return new \Chalcedonyt\RedisTagger\KeyValue\KeyValueProxy;
        });

        $source_config = __DIR__ . '/../config/redis_tagger.php';
        $this->mergeConfigFrom($source_config, 'redis_tagger');

        //commands to generate a kv tag
        $this->app['command.redis_tagger.kv_generate'] = $this->app->share(
            function ($app) {
                return new KVGeneratorCommand($app['config'], $app['view'], $app['files']);
            }
        );
        $this->commands('command.redis_tagger.kv_generate');
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.redis_tagger.kv_generate',];
    }
}
