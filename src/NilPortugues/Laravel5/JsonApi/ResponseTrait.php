<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/18/15
 * Time: 11:19 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApi;

use NilPortugues\Api\JsonApi\Http\ErrorBag;
use NilPortugues\Api\JsonApi\Http\Message\BadRequest;
use NilPortugues\Api\JsonApi\Http\Message\ResourceConflicted;
use NilPortugues\Api\JsonApi\Http\Message\ResourceCreated;
use NilPortugues\Api\JsonApi\Http\Message\ResourceDeleted;
use NilPortugues\Api\JsonApi\Http\Message\ResourceNotFound;
use NilPortugues\Api\JsonApi\Http\Message\ResourceProcessing;
use NilPortugues\Api\JsonApi\Http\Message\ResourceUpdated;
use NilPortugues\Api\JsonApi\Http\Message\Response;
use NilPortugues\Api\JsonApi\Http\Message\UnsupportedAction;
use NilPortugues\Api\JsonApi\Http\PaginatedResource;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

trait ResponseTrait
{
    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function errorResponse(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new BadRequest($errorBag)));
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
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceCreated($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceCreated($json)));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceDeleted()
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceDeleted()));
    }

    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceNotFound(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceNotFound($errorBag)));
    }

    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceConflicted(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceConflicted($errorBag)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceProcessing($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceProcessing($json)));
    }

    /**
     * @param string $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function resourceUpdated($json)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new ResourceUpdated($json)));
    }

    /**
     * @param string|PaginatedResource $json
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function response($json)
    {
        if ($json instanceof PaginatedResource) {
            $json = json_encode($json);
        }

        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new Response($json)));
    }

    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unsupportedAction(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new UnsupportedAction($errorBag)));
    }
}
