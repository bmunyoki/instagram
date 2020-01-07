<?php
namespace bmunyoki\Instagram;
use Illuminate\Support\ServiceProvider;
class InstagramServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bmunyoki');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'bmunyoki');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }
    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/instagram.php', 'instagram');
        // Register the service the package provides.
        $this->app->singleton('instagram', function ($app) {
            return new Instagram;
        });
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['instagram'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/instagram.php' => config_path('instagram.php'),
        ], 'instagram.config');
        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/bmunyoki'),
        ], 'instagram.views');*/
        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/bmunyoki'),
        ], 'instagram.views');*/
        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/bmunyoki'),
        ], 'instagram.views');*/
        // Registering package commands.
        // $this->commands([]);
    }
}