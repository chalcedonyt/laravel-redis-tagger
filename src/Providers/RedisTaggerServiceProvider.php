<?php
namespace Chalcedonyt\RedisTagger\Providers;

use Illuminate\Support\ServiceProvider;
use Chalcedonyt\RedisTagger\Commands\TaggerGeneratorCommand;

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
        $this -> app -> bind('RedisTagger', function(){
            return new \Chalcedonyt\RedisTagger\TaggerProxy;
        });

        $source_config = __DIR__ . '/../config/redis_tagger.php';
        $this->mergeConfigFrom($source_config, 'redis_tagger');

        //commands to generate a kv tag
        $this->app['command.redis_tagger.generate'] = $this->app->share(
            function ($app) {
                return new TaggerGeneratorCommand($app['config'], $app['view'], $app['files']);
            }
        );
        $this->commands('command.redis_tagger.generate');
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['command.redis_tagger.generate',];
    }
}
