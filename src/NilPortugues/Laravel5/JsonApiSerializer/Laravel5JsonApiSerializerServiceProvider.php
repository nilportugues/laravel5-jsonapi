<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/15/15
 * Time: 5:45 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApiSerializer;

use Illuminate\Support\ServiceProvider;
use NilPortugues\Serializer\Serializer;


class Laravel5JsonApiSerializerServiceProvider extends ServiceProvider
{
    const PATH = '/../../../config/jsonapi.php';

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([__DIR__. self::PATH => config('jsonapi.php')]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__. self::PATH, 'jsonapi_mapping');
        $this->app->singleton(\NilPortugues\Serializer\Serializer::class, function($app) {
            return (new JsonApiSerializer())->instance($app['config']->get('jsonapi_mapping'));
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['jsonapi_mapping'];
    }
} 