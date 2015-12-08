<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 10/16/15
 * Time: 8:59 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Mapper;

class Mapper extends \NilPortugues\Api\Mapping\Mapper
{
    /**
     * @param string|array $mappedClass
     *
     * @return array
     */
    protected function buildMapping($mappedClass)
    {
        return (\is_string($mappedClass) && \class_exists($mappedClass, true)) ?
            MappingFactory::fromClass($mappedClass) :
            MappingFactory::fromArray($mappedClass);
    }
}
