<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/2/15
 * Time: 9:37 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use NilPortugues\Api\JsonApi\Server\Data\DataException;
use NilPortugues\Api\JsonApi\Server\Data\DataObject;
use NilPortugues\Api\JsonApi\Server\Errors\Error;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\RequestTrait;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\ResponseTrait;
use NilPortugues\Serializer\Serializer;

/**
 * Class CreateResource.
 */
class CreateResource
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
     * @param array   $data
     * @param Builder $query
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(array $data, Builder $query)
    {
        $errorBag = new ErrorBag();

        try {
            $className = get_class($query->getModel());
            DataObject::assertPost($data, $this->serializer, $className, $errorBag);

            $values = DataObject::getAttributes($data, $this->serializer);
            $model = $this->createResource($data, $query, $values);

            $response = $this->resourceCreated($this->serializer->serialize($model));
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e, $errorBag);
        }

        return $response;
    }

    /**
     * @param array   $data
     * @param Builder $query
     * @param         $values
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function createResource(array $data, Builder $query, $values)
    {
        $model = $query->getModel();
        foreach ($values as $attribute => $value) {
            $model->$attribute = $value;
        }

        if (!empty($data['id'])) {
            $idKey = $model->getKeyName();
            $model->$idKey = $data['id'];
        }
        $model->save();

        return $model;
    }

    /**
     * @param $e
     * @param $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getErrorResponse($e, $errorBag)
    {
        switch (get_class($e)) {
            case DataException::class:
                $response = $this->unprocessableEntity($errorBag);
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
