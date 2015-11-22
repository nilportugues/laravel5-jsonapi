<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 11/22/15
 * Time: 3:26 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace NilPortugues\Laravel5\JsonApi;

use App\Http\Controllers\Controller;
use NilPortugues\Laravel5\JsonApiJsonApiSerializer\RequestTrait;

class JsonApiController extends Controller
{
    use RequestTrait;
    use ResponseTrait;
    use PaginationTrait;
}
