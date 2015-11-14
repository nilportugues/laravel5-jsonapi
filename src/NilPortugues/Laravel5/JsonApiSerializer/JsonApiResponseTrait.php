<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/18/15
 * Time: 11:19 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApiSerializer;

use NilPortugues\Api\JsonApi\Http\Message\ErrorResponse;
use NilPortugues\Api\JsonApi\Http\Message\Request;
use NilPortugues\Api\JsonApi\Http\Message\ResourceCreatedResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourceDeletedResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourceNotFoundResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourcePatchErrorResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourcePostErrorResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourceProcessingResponse;
use NilPortugues\Api\JsonApi\Http\Message\ResourceUpdatedResponse;
use NilPortugues\Api\JsonApi\Http\Message\Response;
use NilPortugues\Api\JsonApi\Http\Message\UnsupportedActionResponse;
use NilPortugues\Serializer\Serializer;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

trait JsonApiResponseTrait
{
    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function errorResponse($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ErrorResponse($json)));
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addHeaders(\Psr\Http\Message\ResponseInterface $response)
    {
        return $response;
    }

    /**
     * @param string $json
     * @param string $locationUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceCreatedResponse($json, $locationUrl)
    {
        $response = new ResourceCreatedResponse($json);
        $response->withAddedHeader('Location', $locationUrl);

        return (new HttpFoundationFactory())->createResponse($this->addHeaders($response));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceDeletedResponse($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceDeletedResponse($json)));
    }

    /**
     * @param $errorTitle
     * @param $errorMessage
     * @param $parentUrl
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceNotFoundResponse($errorTitle, $errorMessage, $parentUrl)
    {
        $json = json_encode([
                'errors' => [
                    'status' => 404,
                    'code' => 'not_found',
                    'title' => $errorTitle,
                    'detail' => $errorMessage,
                ],
                'links' => [
                    'parent' => [
                        'href' => $parentUrl,
                    ],
                ],
            ]);

        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceNotFoundResponse($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourcePatchErrorResponse($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourcePatchErrorResponse($json)));
    }

    /**
     * @param $errorTitle
     * @param $errorMessage
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourcePostErrorResponse($errorTitle, $errorMessage)
    {
        $json = json_encode([
                'errors' => [
                    'status' => 409,
                    'code' => 'Conflict',
                    'title' => $errorTitle,
                    'detail' => $errorMessage,
                ],
            ]);

        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourcePostErrorResponse($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceProcessingResponse($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceProcessingResponse($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceUpdatedResponse($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceUpdatedResponse($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function response($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new Response($json)));
    }

    /**
     * @param            $classMethod
     * @param Serializer $serializer
     * @param Request    $request
     * @param            $value
     * @param            $totalAmount
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function collectionResponse($classMethod, Serializer $serializer, Request $request, $value, $totalAmount)
    {
        $controllerRoute = str_replace('::', '@', '\\'.$classMethod);

        $pageNumber = $request->getPageNumber();
        $resultsPerPage = $request->getPageSize();

        $serializer->getTransformer()->setSelfUrl(
            urldecode(
                action(
                    $controllerRoute,
                    [
                        'page' => array_filter(
                            [
                                'number' => $pageNumber,
                                'size' => $request->getPageSize(),
                                'limit' => $request->getPageLimit(),
                                'cursor' => $request->getPageCursor(),
                                'offset' => $request->getPageOffset(),
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

        if (0 < $pageNumber - 1) {
            $serializer->getTransformer()->setPrevUrl(
                urldecode(
                    action(
                        $controllerRoute,
                        [
                            'page' => array_filter(
                                [
                                    'number' => $pageNumber - 1,
                                    'size' => $request->getPageSize(),
                                    'limit' => $request->getPageLimit(),
                                    'cursor' => $request->getPageCursor(),
                                    'offset' => $request->getPageOffset(),
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

        if ($totalAmount > ($resultsPerPage * $pageNumber)) {
            $serializer->getTransformer()->setNextUrl(
                urldecode(
                    action(
                        $controllerRoute,
                        [
                            'page' => array_filter(
                                [
                                    'number' => $pageNumber + 1,
                                    'size' => $request->getPageSize(),
                                    'limit' => $request->getPageLimit(),
                                    'cursor' => $request->getPageCursor(),
                                    'offset' => $request->getPageOffset(),
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

        $serializer->getTransformer()->setFirstUrl(
            urldecode(
                action(
                    $controllerRoute,
                    [
                        'page' => array_filter(
                            [
                                'number' => 1,
                                'size' => $request->getPageSize(),
                                'limit' => $request->getPageLimit(),
                                'cursor' => $request->getPageCursor(),
                                'offset' => $request->getPageOffset(),
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

        $serializer->getTransformer()->setLastUrl(
            urldecode(
                action(
                    $controllerRoute,
                    array_filter(
                        [
                            'page' => array_filter(
                                [
                                    'number' => ceil($totalAmount / $resultsPerPage),
                                    'size' => $request->getPageSize(),
                                    'limit' => $request->getPageLimit(),
                                    'cursor' => $request->getPageCursor(),
                                    'offset' => $request->getPageOffset(),
                                ]
                            ),
                            'fields' => $request->getQueryParam('fields'),
                            'filter' => $request->getQueryParam('filter'),
                            'sort' => $request->getQueryParam('sort'),
                            'include' => $request->getQueryParam('include'),
                        ]
                    )
                )
            )
        );

        $json = $serializer->serialize($value, $request);

        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new Response($json)));
    }

    /**
     * @param string  $errorTitle
     * @param string  $errorMessage
     * @param string  $classMethod
     * @param Request $request
     * @param int     $totalAmount
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function collectionNotFoundResponse($errorTitle, $errorMessage, $classMethod, Request $request, $totalAmount)
    {
        $controllerRoute = str_replace('::', '@', '\\'.$classMethod);

        $json = json_encode([
                'errors' => [
                    'status' => 404,
                    'code' => 'not_found',
                    'title' => $errorTitle,
                    'detail' => $errorMessage,
                ],
                'links' => [
                    'first' => [
                        'href' => urldecode(
                            action(
                                $controllerRoute,
                                [
                                    'page' => array_filter(
                                        [
                                            'number' => 1,
                                            'size' => $request->getPageSize(),
                                            'limit' => $request->getPageLimit(),
                                            'cursor' => $request->getPageCursor(),
                                            'offset' => $request->getPageOffset(),
                                        ]
                                    ),
                                    'fields' => $request->getQueryParam('fields'),
                                    'filter' => $request->getQueryParam('filter'),
                                    'sort' => $request->getQueryParam('sort'),
                                    'include' => $request->getQueryParam('include'),
                                ]
                            )
                        ),
                    ],
                    'last' => [
                        'href' => urldecode(
                            action(
                                $controllerRoute,
                                array_filter(
                                    [
                                        'page' => array_filter(
                                            [
                                                'number' => ceil($totalAmount / $request->getPageSize()),
                                                'size' => $request->getPageSize(),
                                                'limit' => $request->getPageLimit(),
                                                'cursor' => $request->getPageCursor(),
                                                'offset' => $request->getPageOffset(),
                                            ]
                                        ),
                                        'fields' => $request->getQueryParam('fields'),
                                        'filter' => $request->getQueryParam('filter'),
                                        'sort' => $request->getQueryParam('sort'),
                                        'include' => $request->getQueryParam('include'),
                                    ]
                                )
                            )
                        ),
                    ],
                ],
            ]);

        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceNotFoundResponse($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unsupportedActionResponse($json)
    {
        return (new HttpFoundationFactory())
            ->createResponse(
                $this->addHeaders(new UnsupportedActionResponse($json))
            );
    }
}
