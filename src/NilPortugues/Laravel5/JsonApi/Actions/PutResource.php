<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/2/15
 * Time: 9:38 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use NilPortugues\Api\JsonApi\Server\Data\DataException;
use NilPortugues\Api\JsonApi\Server\Data\DataObject;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Api\JsonApi\Server\Errors\Error;
use NilPortugues\Api\JsonApi\Server\Errors\NotFoundError;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\RequestTrait;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\ResponseTrait;
use NilPortugues\Serializer\Serializer;

/**
 * Class PutResource.
 */
class PutResource
{
    use RequestTrait;
    use ResponseTrait;

    /**
     * @var ErrorBag
     */
    private $errorBag;

    /**
     * @var \NilPortugues\Serializer\Serializer
     */
    private $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
        $this->errorBag = new ErrorBag();
    }

    /**
     * @param         $id
     * @param array   $data
     * @param Builder $query
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id, array $data, Builder $query)
    {
        try {
            $className = get_class($query->getModel());
            DataObject::assertPut($data, $this->serializer, $className, $this->errorBag);

            $model = $this->getResource($id, $query);

            if (empty($model)) {
                $mapping = $this->serializer->getTransformer()->getMappingByClassName($className);

                return $this->resourceNotFound(new ErrorBag([new NotFoundError($mapping->getClassAlias(), $id)]));
            }

            $this->updateResource($data, $model);
            $response = $this->resourceUpdated($this->serializer->serialize($model));
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e);
        }

        return $response;
    }

    /**
     * @param         $id
     * @param Builder $query
     *
     * @return Model|null
     */
    private function getResource($id, Builder $query)
    {
        $idKey = $query->getModel()->getKeyName();
        $model = $query->getModel()->query()->where($idKey, '=', $id)->first();

        return $model;
    }

    /**
     * @param array $data
     * @param Model $model
     */
    private function updateResource(array $data, Model $model)
    {
        $values = DataObject::getAttributes($data, $this->serializer);
        foreach ($values as $attribute => $value) {
            $model->$attribute = $value;
        }
        $model->update();
    }

    /**
     * @param Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getErrorResponse(Exception $e)
    {
        switch (get_class($e)) {
            case DataException::class:
                $response = $this->unprocessableEntity($this->errorBag);
                break;

            default:
                $response = $this->errorResponse(
                    new ErrorBag([new Error('Bad Request', 'Request could not be served.')])
                );

                return $response;
        }

        return $response;
    }
}
