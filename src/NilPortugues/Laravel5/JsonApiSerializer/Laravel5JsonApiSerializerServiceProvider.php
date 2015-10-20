<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/15/15
 * Time: 5:45 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApiSerializer;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Laravel5\JsonApiSerializer\Mapper\Mapper;

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
     */
    public function boot()
    {
        $this->publishes([__DIR__ . self::PATH => config('jsonapi.php')]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . self::PATH, 'jsonapi');
        $this->app->singleton(
            \NilPortugues\Laravel5\JsonApiSerializer\JsonApiSerializer::class,
            function ($app) {

                $mapping = $app['config']->get('jsonapi');
                $key     = md5(json_encode($mapping));

                return Cache::rememberForever(
                    $key,
                    function () use ($mapping) {
                        self::parseNamedRoutes($mapping);

                        return new JsonApiSerializer(new JsonApiTransformer(new Mapper($mapping)));
                    }
                );
            }
        );
    }

    /**
     * @param array $mapping
     *
     * @return mixed
     */
    private static function parseNamedRoutes(array &$mapping)
    {
        foreach ($mapping as &$map) {
            self::parseUrls($map);
            self::parseRelationshipUrls($map);
        }
    }

    /**
     * @param array $map
     */
    private static function parseUrls(&$map)
    {
        if (!empty($map['urls'])) {
            foreach ($map['urls'] as &$namedUrl) {
                $namedUrl = urldecode(route($namedUrl));
            }
        }
    }

    /**
     * @param array $map
     */
    private static function parseRelationshipUrls(&$map)
    {
        if (!empty($map['relationships'])) {
            foreach ($map['relationships'] as &$relationship) {
                foreach ($relationship as &$namedRelationship) {
                    $namedRelationship = urldecode(route($namedRelationship));
                }
            }
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['jsonapi'];
    }
}
