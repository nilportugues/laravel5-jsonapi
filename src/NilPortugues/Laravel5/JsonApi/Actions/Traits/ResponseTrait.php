<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 8/18/15
 * Time: 11:19 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Actions\Traits;

use NilPortugues\Api\JsonApi\Http\PaginatedResource;
use NilPortugues\Api\JsonApi\Http\Response\BadRequest;
use NilPortugues\Api\JsonApi\Http\Response\ResourceConflicted;
use NilPortugues\Api\JsonApi\Http\Response\ResourceCreated;
use NilPortugues\Api\JsonApi\Http\Response\ResourceDeleted;
use NilPortugues\Api\JsonApi\Http\Response\ResourceNotFound;
use NilPortugues\Api\JsonApi\Http\Response\ResourceProcessing;
use NilPortugues\Api\JsonApi\Http\Response\ResourceUpdated;
use NilPortugues\Api\JsonApi\Http\Response\Response;
use NilPortugues\Api\JsonApi\Http\Response\UnprocessableEntity;
use NilPortugues\Api\JsonApi\Http\Response\UnsupportedAction;
use NilPortugues\Api\JsonApi\Server\Errors\ErrorBag;
use Psr\Http\Message\ResponseInterface;
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
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function addHeaders(ResponseInterface $response)
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

    /**
     * @param ErrorBag $errorBag
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unprocessableEntity(ErrorBag $errorBag)
    {
        return (new HttpFoundationFactory())->createResponse($this->addHeaders(new UnprocessableEntity($errorBag)));
    }
}
