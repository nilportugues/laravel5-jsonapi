<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/28/15
 * Time: 8:03 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use NilPortugues\Api\JsonApi\Http\PaginatedResource;
use NilPortugues\Api\JsonApi\Server\Errors\Error;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Api\JsonApi\Server\Errors\OufOfBoundsError;
use NilPortugues\Api\JsonApi\Server\Query\QueryException;
use NilPortugues\Api\JsonApi\Server\Query\QueryObject;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\PaginationTrait;
use NilPortugues\Laravel5\JsonApi\Eloquent\EloquentHelper;
use NilPortugues\Serializer\Serializer;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\RequestTrait;
use NilPortugues\Laravel5\JsonApi\Actions\Traits\ResponseTrait;

/**
 * Class ListResource.
 */
class ListResource
{
    use RequestTrait;
    use PaginationTrait;
    use ResponseTrait;

    /**
     * @var ErrorBag
     */
    private $errorBag;
    /**
     * @var int
     */
    private $pageNumber;
    /**
     * @var int
     */
    private $pageSize;

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
        $this->pageNumber = $this->apiRequest()->getPageNumber();
        $this->pageSize = $this->apiRequest()->getPageSize();
    }

    /**
     * @param Builder $query
     * @param         $namedRoute
     * @param array   $namedRouteParams
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(Builder $query, $namedRoute, array $namedRouteParams = [])
    {
        $baseQuery = clone $query;

        try {
            QueryObject::assert($this->serializer, $this->errorBag);
            $totalAmount = $this->getTotalAmount($baseQuery);

            if ($totalAmount > 0 && $this->pageNumber > ceil($totalAmount / $this->pageSize)) {
                return $this->resourceNotFound(
                    new ErrorBag([new OufOfBoundsError($this->pageNumber, $this->pageSize)])
                );
            }

            $paginatedResource = $this->getPaginatedResource(
                $this->serializer,
                $namedRoute,
                $namedRouteParams,
                $totalAmount,
                $baseQuery
            );

            $response = $this->response($paginatedResource);
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e);
        }

        return $response;
    }

    /**
     * @param Builder $baseQuery
     *
     * @return mixed
     */
    private function getTotalAmount(Builder $baseQuery)
    {
        $totalAmountQuery = clone $baseQuery;
        $idKey = $totalAmountQuery->getModel()->getKeyName();

        return $totalAmountQuery->get([$idKey])->count();
    }

    /**
     * @param Serializer $serializer
     * @param string     $namedRoute
     * @param array      $namedRouteParams
     * @param int        $totalAmount
     * @param Builder    $baseQuery
     *
     * @return PaginatedResource
     */
    private function getPaginatedResource(
        Serializer $serializer,
        $namedRoute,
        array $namedRouteParams,
        $totalAmount,
        Builder $baseQuery
    ) {
        $links = $this->pagePaginationLinks(
            $namedRoute,
            $namedRouteParams,
            $this->pageNumber,
            $this->pageSize,
            $totalAmount
        );

        $paginatedResource = new PaginatedResource(
            $serializer->serialize($this->getPaginatedResults($serializer, $baseQuery)),
            $this->pageNumber,
            $this->pageSize,
            $totalAmount,
            $links
        );

        return $paginatedResource;
    }

    /**
     * @param Serializer $serializer
     * @param Builder    $baseQuery
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPaginatedResults(Serializer $serializer, Builder $baseQuery)
    {
        $pageQuery = clone $baseQuery;
        $model = EloquentHelper::paginate($serializer, $pageQuery)->get();

        return $model;
    }

    /**
     * @param Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getErrorResponse(Exception $e)
    {
        switch (get_class($e)) {
            case QueryException::class:
                $response = $this->errorResponse($this->errorBag);
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
