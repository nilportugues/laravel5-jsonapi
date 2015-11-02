<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/16/15
 * Time: 4:43 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApiSerializer;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Serializer\DeepCopySerializer;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer extends DeepCopySerializer
{
    /**
     * @param JsonApiTransformer $strategy
     */
    public function __construct(JsonApiTransformer $strategy)
    {
        parent::__construct($strategy);
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
        if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
            $items = [];
            foreach ($value as &$v) {
                $items[] = $this->serializeObject($v);
            }

            return [self::MAP_TYPE => 'array', self::SCALAR_VALUE => $items];
        }

        if (\is_subclass_of($value, Model::class, true)) {
            $stdClass = (object) $value->getAttributes();
            $data = $this->serializeData($stdClass);
            $data[self::CLASS_IDENTIFIER_KEY] = \get_class($value);

            $methods = $this->getRelationshipMethodsAsPropertyName(
                $value,
                \get_class($value),
                new ReflectionClass($value)
            );

            if (!empty($methods)) {
                $data = \array_merge($data, $methods);
            }

            return $data;
        }

        return parent::serializeObject($value);
    }

    /**
     * @param                 $value
     * @param string          $className
     * @param ReflectionClass $reflection
     *
     * @return array
     */
    protected function getRelationshipMethodsAsPropertyName($value, $className, ReflectionClass $reflection)
    {
        $methods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (\ltrim($method->class, '\\') === \ltrim($className, '\\')) {
                $name = $method->name;
                $reflectionMethod = $reflection->getMethod($name);

                // Eloquent relations do not include parameters, so we'll be filtering based on this criteria.
                if (0 == $reflectionMethod->getNumberOfParameters()) {
                    try {
                        $returned = $reflectionMethod->invoke($value);
                        //All operations (eg: boolean operations) are now filtered out.
                        if (\is_object($returned)) {

                            // Only keep those methods as properties if these are returning Eloquent relations.
                            // But do not run the operation as it is an expensive operation.
                            if (false !== \strpos(\get_class($returned), 'Illuminate\Database\Eloquent\Relations')) {
                                $items = [];
                                foreach ($returned->getResults() as $model) {
                                    if (\is_object($model)) {
                                        $stdClass = (object) $model->getAttributes();
                                        $data = $this->serializeData($stdClass);
                                        $data[self::CLASS_IDENTIFIER_KEY] = \get_class($model);

                                        $items[] = $data;
                                    }
                                }
                                if (!empty($items)) {
                                    $methods[$name] = [self::MAP_TYPE => 'array', self::SCALAR_VALUE => $items];
                                }
                            }
                        }
                    } catch (ErrorException $e) {
                    }
                }
            }
        }

        return $methods;
    }
}
