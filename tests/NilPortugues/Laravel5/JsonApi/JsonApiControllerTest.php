<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/9/15
 * Time: 3:05 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Laravel5\JsonApi;

/**
 * Class JsonApiControllerTest.
 */
class JsonApiControllerTest extends LaravelTestCase
{
    public function testListAction()
    {
        $response = $this->call('GET', 'http://localhost/api/v1/employees');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetDataModel()
    {
    }

    public function testGetAction()
    {
    }

    public function testPostAction()
    {
    }

    public function testPatchAction()
    {
    }

    public function testPutAction()
    {
    }

    public function testDeleteAction()
    {
    }
}
