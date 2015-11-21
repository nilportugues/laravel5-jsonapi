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

use NilPortugues\Laravel5\JsonApiSerializer\Factory\RequestFactory;

/**
 * Class RequestTrait.
 */
trait RequestTrait
{
    /**
     * @return \NilPortugues\Api\JsonApi\Http\Message\Request
     */
    protected function buildJsonApiRequest()
    {
        return RequestFactory::create();
    }
}
