<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/21/15
 * Time: 3:17 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApiSerializer\Factory;

use NilPortugues\Api\JsonApi\Http\Message\Request;

/**
 * Class RequestFactory.
 */
class RequestFactory
{
    /**
     * @var Request
     */
    private static $request;

    /**
     * @return Request
     */
    public static function create()
    {
        if (self::$request) {
            return self::$request;
        }

        self::$request = new Request();

        return self::$request;
    }
}
