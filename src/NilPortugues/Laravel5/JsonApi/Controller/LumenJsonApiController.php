<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 13/01/16
 * Time: 19:57.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Laravel5\JsonApi\Controller;

use Laravel\Lumen\Routing\Controller;

/**
 * Class LumenJsonApiController.
 */
abstract class LumenJsonApiController extends Controller
{
    use JsonApiTrait;
}
