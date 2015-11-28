<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/21/15
 * Time: 11:53 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApi;

use Illuminate\Container\Container;
use NilPortugues\Api\JsonApi\Http\Message\Request;
use NilPortugues\Api\JsonApi\Http\Factory\RequestFactory;

/**
 * Class PaginationTrait.
 */
trait PaginationTrait
{
    /**
     * @param string $routeName
     * @param array  $routeParams
     * @param int    $pageNumber
     * @param int    $pageSize
     * @param int    $totalPages
     *
     * @return array
     */
    protected function pagePaginationLinks($routeName, array $routeParams = [], $pageNumber, $pageSize, $totalPages)
    {
        $request = RequestFactory::create();

        $next = $pageNumber + 1;
        $previous = $pageNumber - 1;
        $last = ceil($totalPages / $pageSize);

        $links = array_filter([
                'self' => $pageNumber,
                'first' => 1,
                'next' => ($next <= $last) ? $next : null,
                'previous' => ($previous > 1) ? $previous : null,
                'last' => $last,
            ]);

        foreach ($links as &$numberedLink) {
            $numberedLink = $this->pagePaginatedRoute($request, $routeName, $routeParams, $numberedLink, $pageSize);
        }

        return $links;
    }

    /**
     * Build the URL using Laravel's route facade method.
     *
     * @param Request $request
     * @param string  $routeName
     * @param array   $routeParams
     * @param int     $pageNumber
     * @param int     $pageSize
     *
     * @return string
     */
    private function pagePaginatedRoute($request, $routeName, array $routeParams = [], $pageNumber, $pageSize)
    {
        return urldecode(
            Container::getInstance()->make('url')->route(
                $routeName,
                array_merge(
                    $routeParams,
                    [
                        'page' => array_filter(
                            [
                                'number' => $pageNumber,
                                'size' => $pageSize,
                            ]
                        ),
                        'fields' => $request->getQueryParam('fields'),
                        'filter' => $request->getQueryParam('filter'),
                        'sort' => $request->getQueryParam('sort'),
                        'include' => $request->getQueryParam('include'),
                    ]
                )
            )
        );
    }
}
