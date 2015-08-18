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
use NilPortugues\Serializer\Serializer;

/**
 * Class JsonApiSerializer.
 */
class JsonApiSerializer extends Serializer
{
    /**
     * @param JsonApiTransformer $strategy
     */
    public function __construct(JsonApiTransformer $strategy)
    {
        parent::__construct($strategy);
    }
}
