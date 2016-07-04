<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 5/04/16
 * Time: 0:15.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use NilPortugues\Api\JsonApi\Http\PaginatedResource;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use NilPortugues\Api\JsonApi\Server\Errors\OufOfBoundsError;
use NilPortugues\Api\JsonApi\Server\Query\QueryObject;

/**
 * Class ListResource.
 */
class ListResource extends \NilPortugues\Api\JsonApi\Server\Actions\ListResource
{
    /**
     * @param \Exception $e
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function getErrorResponse(\Exception $e)
    {
        if (config('app.debug')) {
            throw $e;
        }

        return parent::getErrorResponse($e);
    }


    /**
     * @param callable $totalAmountCallable
     * @param callable $resultsCallable
     * @param string   $route
     * @param string   $className
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function get(callable $totalAmountCallable, callable $resultsCallable, $route, $className)
    {
        try {
            QueryObject::assert(
                $this->serializer,
                $this->fields,
                $this->included,
                $this->sorting,
                $this->errorBag,
                $className
            );
            $totalAmount = $totalAmountCallable();

            if ($totalAmount > 0 && $this->page->size() > 0 && $this->page->number() > ceil($totalAmount / $this->page->size())) {
                return $this->resourceNotFound(
                    new ErrorBag([new OufOfBoundsError($this->page->number(), $this->page->size())])
                );
            }

            $links = $this->pagePaginationLinks(
                $route,
                $this->page->number(),
                $this->page->size(),
                $totalAmount,
                $this->fields,
                $this->sorting,
                $this->included,
                $this->filters
            );


            $results = $resultsCallable();

            if ($results instanceof Collection) {
                $results = json_encode(['data' => $results->toArray()]);
            }


            $paginatedResource = new PaginatedResource(
                $results,
                $this->page->number(),
                $this->page->size(),
                $totalAmount,
                $links
            );

            $response = $this->response($paginatedResource);
        } catch (Exception $e) {
            $response = $this->getErrorResponse($e);
        }

        return $response;
    }

}
