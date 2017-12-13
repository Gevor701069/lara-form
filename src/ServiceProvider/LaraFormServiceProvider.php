<?php

namespace LaraForm\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use LaraForm\FormBuilder;
use LaraForm\FormProtection;
use LaraForm\Middleware\LaraFormMiddleware;
use LaraForm\Stores\ErrorStore;
use LaraForm\Stores\OldInputStore;

class LaraFormServiceProvider extends ServiceProvider
{
    /**
     *
     */
    public function register()
    {
        $this->registerFormProtection();
        $this->registerMiddleware(LaraFormMiddleware::class);
        $this->registerStores();
        $this->registerFormBuilder();
        $this->configMerge();
        $this->setCoreConfig();

    }

    /**
     *
     */
    public function setCoreConfig()
    {
        $baseConfig = require_once dirname (__DIR__) . '/Config/lara_form_core.php';
        $this->app['config']->set('lara_form_core', $baseConfig);
    }

    /**
     *
     */
    public function boot()
    {
        $this->publishes([
            dirname (__DIR__) .'/Config/lara_form_base.php' => config_path('lara_form.php'),
        ]);
    }


    /**
     *
     */
    protected function configMerge()
    {
        $this->replaceConfig(
            dirname (__DIR__) . '/Config/lara_form_base.php', 'lara_form'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     * @param  string $path
     * @param  string $key
     * @return void
     */
    protected function replaceConfig($path, $key)
    {
        $config = $this->app['config']->get($key, []);
        $baseConfig = require_once $path;

        $this->app['config']->set($key, array_replace_recursive($baseConfig,$config));
    }

    /**
     * Register the Debugbar Middleware
     * @param  string $middleware
     */
    protected function registerMiddleware($middleware)
    {
        $router = $this->app['router'];
        $router->pushMiddlewareToGroup('web', $middleware);
    }


    /**
     *
     */
    protected function registerFormProtection()
    {
        $this->app->singleton('laraform.protection', function ($app) {
            return new FormProtection();
        });
    }


    /**
     *
     */
    protected function registerStores()
    {
        $this->app->singleton('laraform.error', function ($app) {
            return new ErrorStore();
        });
        $this->app->singleton('laraform.oldInput', function ($app) {
            return new OldInputStore();
        });
    }

    /**
     *
     */
    protected function registerFormBuilder()
    {

        $this->app->singleton('laraform', function ($app) {
            return new FormBuilder(
                $app['laraform.protection'],
                $app['laraform.error'],
                $app['laraform.oldInput']
            );
        });
    }

    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['laraform'];
    }
}
