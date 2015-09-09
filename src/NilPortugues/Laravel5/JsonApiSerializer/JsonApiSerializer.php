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

use Illuminate\Database\Eloquent\Model;
use NilPortugues\Api\JsonApi\JsonApiTransformer;
use NilPortugues\Serializer\DeepCopySerializer;

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

        if (is_subclass_of($value, Model::class, true)) {
            $stdClass = (object) $value->getAttributes();
            $data =  $this->serializeData($stdClass);
            $data[self::CLASS_IDENTIFIER_KEY] = get_class($value);
            return $data;
        }

        return parent::serializeObject($value);
    }    
}
