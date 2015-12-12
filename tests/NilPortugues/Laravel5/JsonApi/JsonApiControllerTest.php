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
    /**
     * Setup DB before each test.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * This is required for \Symfony\Bridge\PsrHttpMessage\Factory to work.
     * This comes as a trade-off of building the underlying package as framework-agnostic.
     *
     * @param string $method
     * @param string $server
     * @param string $uri
     */
    protected function serverEnvironment($method, $server, $uri)
    {
        $_SERVER['REQUEST_METHOD'] = strtoupper($method);
        $_SERVER['SERVER_NAME'] = str_replace(['http://', 'https://'], '', $server);
        $_SERVER['REQUEST_URI'] = $uri;
    }

    public function testListAction()
    {
        $this->serverEnvironment('GET', 'example.com', '/api/v1/employees');
        $response = $this->call('GET', 'http://example.com/api/v1/employees');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetActionWhenEmployeeDoesNotExist()
    {
        $this->serverEnvironment('GET', 'example.com', '/api/v1/employees/1000');
        $response = $this->call('GET', 'http://example.com/api/v1/employees/1000');

        $this->assertEquals(404, $response->getStatusCode());
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

    public function testDeleteActionWhenEmployeeDoesNotExistReturns404()
    {
        $this->serverEnvironment('DELETE', 'example.com', '/api/v1/employees/1000');
        $response = $this->call('DELETE', 'http://example.com/api/v1/employees/1000');

        $this->assertEquals(404, $response->getStatusCode());
    }
}
