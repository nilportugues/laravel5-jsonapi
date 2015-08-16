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

use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Api\Mapping\Mapper;
use NilPortugues\Serializer\Serializer;

/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer
{
    /**
     * @param array $mapping
     *
     * @return Serializer
     */
    public static function instance(array $mapping)
    {
        return new Serializer(new JsonApiTransformer(new Mapper($mapping)));
    }
}
