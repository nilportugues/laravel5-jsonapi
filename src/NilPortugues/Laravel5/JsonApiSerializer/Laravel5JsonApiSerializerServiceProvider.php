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

use Illuminate\Support\ServiceProvider;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Api\Mapping\Mapper;

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
        $this->publishes([__DIR__.self::PATH => config('jsonapi.php')]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.self::PATH, 'jsonapi');
        $this->app->singleton(\NilPortugues\Laravel5\JsonApiSerializer\JsonApiSerializer::class, function ($app) {

                $mapping = $app['config']->get('jsonapi');
                $key = md5(json_encode($mapping));

                $cachedMapping = Cache::get($key);
                if(!empty($cachedMapping)) {
                    return unserialize($cachedMapping);
                }

                foreach($mapping as &$map) {
                    self::parseUrls($map);
                    self::parseRelationshipUrls($map);
                }

                $serializer = new JsonApiSerializer(new JsonApiTransformer(new Mapper($mapping)));
                Cache::put($key, serialize($serializer),60*60*24);

                return $serializer;
            });
    }


    /**
     * @param array $map
     */
    private static function parseUrls(array &$map)
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
    private static function parseRelationshipUrls(array &$map)
    {
        if (!empty($map['relationships'])) {
            foreach ($map['relationships'] as &$relationship) {
                foreach($relationship as &$namedRelationship) {
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
