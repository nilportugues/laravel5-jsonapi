<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/9/15
 * Time: 2:57 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Laravel5\JsonApi\Controller;

use Illuminate\Database\Eloquent\Model;
use NilPortugues\Laravel5\JsonApi\Controller\JsonApiController;

/**
 * Class DummyJsonApiController.
 */
class DummyJsonApiController extends JsonApiController
{
    /**
     * Returns an Eloquent Model.
     *
     * @return Model
     */
    public function getDataModel()
    {
    }
}
