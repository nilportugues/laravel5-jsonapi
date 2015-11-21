<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/14/15
 * Time: 11:46 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApiSerializer;

use NilPortugues\Api\JsonApi\Http\Message\Request as JsonApiRequest;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestTrait.
 */
trait RequestTrait
{
    /**
     * @param Request $request
     *
     * @return JsonApiRequest
     */
    protected function buildJsonApiRequest(Request $request)
    {
        $psr7Factory = new DiactorosFactory();
        $psrRequest = $psr7Factory->createRequest($request);
        $jsonApiRequest = new JsonApiRequest($psrRequest);

        return $jsonApiRequest;
    }
}
