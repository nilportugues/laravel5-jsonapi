<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/14/15
 * Time: 11:46 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApiJsonApiSerializer;

use NilPortugues\Api\JsonApi\Http\Error;
use NilPortugues\Api\JsonApi\Http\Factory\RequestFactory;
use NilPortugues\Api\JsonApi\Http\Message\Request;
use NilPortugues\Laravel5\JsonApi\JsonApiSerializer;

/**
 * Class RequestTrait.
 */
trait RequestTrait
{
    /**
     * @var Error[]
     */
    private $queryParamErrorBag = [];

    /**
     * @return Error[]
     */
    protected function getQueryParamsErrors()
    {
        return $this->queryParamErrorBag;
    }

    /**
     * @param JsonApiSerializer $serializer
     *
     * @return bool
     */
    protected function hasValidQueryParams(JsonApiSerializer $serializer)
    {
        $apiRequest = $this->jsonApiRequest();
        $this->validateQueryParamsTypes($serializer, $apiRequest->getFields());

        return !empty($this->queryParamErrorBag);
    }

    /**
     * @return Request
     */
    protected function jsonApiRequest()
    {
        return RequestFactory::create();
    }

    /**
     * @param JsonApiSerializer $serializer
     * @param array             $fields
     */
    private function validateQueryParamsTypes(JsonApiSerializer $serializer, array $fields)
    {
        if (!empty($fields)) {
            $mappings = $serializer->getTransformer()->getMappings();

            $validateFields = array_keys($fields);
            foreach ($mappings as $mapping) {
                foreach ($validateFields as $key => &$field) {
                    if (0 === strcasecmp($mapping->getClassAlias(), $field)) {
                        unset($validateFields[$key]);
                    }
                }
            }

            if (false === empty($validateFields)) {
                foreach ($validateFields as $field) {
                    $this->queryParamErrorBag[] = new Error(
                        'Invalid Fields Parameter',
                        sprintf("The resource type '%s' does not exist.", $field)
                    );
                }
            }
        }
    }
}
