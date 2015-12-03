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
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Api\JsonApi\Server\Errors\NotFoundError;
use NilPortugues\Api\JsonApi\Server\Query\QueryException;
use NilPortugues\Api\JsonApi\Server\Query\QueryObject;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\RequestTrait;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\ResponseTrait;
use NilPortugues\Serializer\Serializer;

/**
 * Class GetResource.
 */
class GetResource
{
    use RequestTrait;
    use ResponseTrait;

    /**
     * @var \NilPortugues\Api\JsonApi\Server\Errors\ErrorBag
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
     * @param Builder $query
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get($id, Builder $query)
    {
        try {
            QueryObject::assert($this->serializer, $this->errorBag);
            $model = $this->getResource($id, $query);
            $response = $this->response($this->serializer->serialize($model, $this->apiRequest()));
        } catch (Exception $e) {
            $response = $this->getErrorResponse($id, $query, $e);
        }

        return $response;
    }

    /**
     * @param         $id
     * @param Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    private function getResource($id, Builder $query)
    {
        $idKey = $query->getModel()->getKeyName();
        $model = $query->getModel()->query()->where($idKey, '=', $id)->first();

        return $model;
    }

    /**
     * @param           $id
     * @param Builder   $query
     * @param Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getErrorResponse($id, Builder $query, Exception $e)
    {
        switch (get_class($e)) {
            case QueryException::class:
                $response = $this->errorResponse($this->errorBag);
                break;

            default:
                $className = get_class($query->getModel());
                $mapping = $this->serializer->getTransformer()->getMappingByClassName($className);

                $response = $this->resourceNotFound(
                    new ErrorBag([new NotFoundError($mapping->getClassAlias(), $id)])
                );
        }

        return $response;
    }
}
