<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/16/15
 * Time: 4:43 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi;

use NilPortugues\Api\JsonApi\Http\Factory\RequestFactory;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Serializer\DeepCopySerializer;
use NilPortugues\Serializer\Drivers\Eloquent\EloquentDriver;

/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer extends DeepCopySerializer
{
    /**
     * @var JsonApiTransformer
     */
    protected $serializationStrategy;

    /**
     * @param JsonApiTransformer $strategy
     */
    public function __construct(JsonApiTransformer $strategy)
    {
        parent::__construct($strategy);
    }

    /**
     * @param mixed $value
     *
     * @return string
     */
    public function serialize($value)
    {
        $mappings = $this->serializationStrategy->getMappings();
        $request = RequestFactory::create();

        if ($filters = $request->getFields()) {
            foreach ($filters as $type => $properties) {
                foreach ($mappings as $mapping) {
                    if ($mapping->getClassAlias() === $type) {
                        $mapping->setFilterKeys($properties);
                    }
                }
            }
        }

        return parent::serialize($value);
    }
    /**
     * Extract the data from an object.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function serializeObject($value)
    {
        $serialized = EloquentDriver::serialize($value);
        if ($value !== $serialized) {
            return $serialized;
        }

        return parent::serializeObject($value);
    }

    /**
     * @return JsonApiTransformer
     */
    public function getTransformer()
    {
        return $this->serializationStrategy;
    }
}
