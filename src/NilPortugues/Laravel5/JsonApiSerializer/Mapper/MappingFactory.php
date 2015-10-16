<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 10/16/15
 * Time: 8:59 PM
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApiSerializer\Mapper;

use ErrorException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;

/**
 * Class MappingFactory
 * @package NilPortugues\Laravel5\JsonApiSerializer\Mapper
 */
class MappingFactory extends \NilPortugues\Api\Mapping\MappingFactory
{
    /**
     * @var array
     */
    protected static $eloquentClasses = [];

    /**
     * @param string $className
     *
     * @return array
     */
    protected static function getClassProperties($className)
    {
        if (class_exists($className, true)) {
            $reflection = new ReflectionClass($className);
            $value = $reflection->newInstanceWithoutConstructor();

            if (is_subclass_of($value, Model::class, true)) {
                $attributes =  array_merge(
                    Schema::getColumnListing($value->getTable()),
                    self::getRelationshipMethodsAsPropertyName($value, $className, $reflection)
                );

                self::$eloquentClasses[$className] = $attributes;

                return self::$eloquentClasses[$className];
            }

        }

        return parent::getClassProperties($className);
    }


    /**
     * @param                 $value
     * @param string          $className
     * @param ReflectionClass $reflection
     *
     * @return array
     */
    protected static function getRelationshipMethodsAsPropertyName($value, $className, ReflectionClass $reflection)
    {
        $methods = [];
        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (ltrim($method->class, "\\") === ltrim($className, "\\")) {

                $name = $method->name;
                $reflectionMethod = $reflection->getMethod($name);

                // Eloquent relations do not include parameters, so we'll be filtering based on this criteria.
                if (0 == $reflectionMethod->getNumberOfParameters()) {
                    try {
                        $returned = $reflectionMethod->invoke($value);
                        //All operations (eg: boolean operations) are now filtered out.
                        if (is_object($returned)) {

                            // Only keep those methods as properties if these are returning Eloquent relations.
                            // But do not run the operation as it is an expensive operation.
                            if (false !== strpos(get_class($returned), 'Illuminate\Database\Eloquent\Relations')) {
                                $methods[] = $method->name;
                            }

                        }
                    } catch(ErrorException $e) {}
                }
            }
        }

        return $methods;
    }
}
