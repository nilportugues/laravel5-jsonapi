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
        $_SERVER['CONTENT_TYPE'] = 'application/json';
    }

    /**
     * @test
     */
    public function testListAction()
    {
        $this->serverEnvironment('GET', 'localhost', '/api/v1/employees');
        $response = $this->call('GET', 'http://localhost/api/v1/employees');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }

    /**
     * @test
     */
    public function testGetAction()
    {
        $content = <<<JSON
{
    "data": {
        "type": "employee",
        "attributes": {
            "company": "NilPortugues.com",
            "surname": "Portugués",
            "first_name": "Nil",
            "email_address": "nilportugues@localhost",
            "job_title": "Web Developer",
            "business_phone": "(123)555-0100",
            "home_phone": "(123)555-0102",
            "mobile_phone": null,
            "fax_number": "(123)555-0103",
            "address": "Plaça Catalunya 1",
            "city": "Barcelona",
            "state_province": "Barcelona",
            "zip_postal_code": "08028",
            "country_region": "Spain",
            "web_page": "http://nilportugues.com",
            "notes": null,
            "attachments": null
        }
    }
}
JSON;
        $this->serverEnvironment('POST', 'localhost', '/api/v1/employees');
        $this->call('POST', 'http://localhost/api/v1/employees', json_decode($content, true), [], [], []);

        $this->serverEnvironment('GET', 'localhost', '/api/v1/employees/1');
        $response = $this->call('GET', 'http://localhost/api/v1/employees/1');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }

    /**
     * @test
     */
    public function testGetActionWhenEmployeeDoesNotExist()
    {
        $this->serverEnvironment('GET', 'localhost', '/api/v1/employees/1000');
        $response = $this->call('GET', 'http://localhost/api/v1/employees/1000');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }

    /**
     * @test
     */
    public function testPostAction()
    {
        $content = <<<JSON
{
    "data": {
        "type": "employee",
        "attributes": {
            "company": "NilPortugues.com",
            "surname": "Portugués",
            "first_name": "Nil",
            "email_address": "nilportugues@localhost",
            "job_title": "Web Developer",
            "business_phone": "(123)555-0100",
            "home_phone": "(123)555-0102",
            "mobile_phone": null,
            "fax_number": "(123)555-0103",
            "address": "Plaça Catalunya 1",
            "city": "Barcelona",
            "state_province": "Barcelona",
            "zip_postal_code": "08028",
            "country_region": "Spain",
            "web_page": "http://nilportugues.com",
            "notes": null,
            "attachments": null
        }
    }
}
JSON;
        $this->serverEnvironment('POST', 'localhost', '/api/v1/employees');
        $response = $this->call('POST', 'http://localhost/api/v1/employees', json_decode($content, true), [], [], []);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
        $this->assertEquals('http://localhost/api/v1/employees/1', $response->headers->get('Location'));
    }

    /**
     * @test
     */
    public function testPatchActionWhenEmployeeDoesNotExistReturns400()
    {
        $content = <<<JSON
{
  "data": {
    "type": "employee",
    "id": 1000,
    "attributes": {
      "email_address": "nilopc@github.com"
    }
  }
}
JSON;
        $this->serverEnvironment('PATCH', 'localhost', '/api/v1/employees/1000');
        $response = $this->call('PATCH', 'http://localhost/api/v1/employees/1000', json_decode($content, true), [], [], []);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }

    /**
     * @test
     */
    public function testPutActionWhenEmployeeDoesNotExistReturns400()
    {
        $content = <<<JSON
{
  "data": {
    "type": "employee",
    "id": 1000,
    "attributes": {
          "company": "NilPortugues.com",
          "surname": "Portugués",
          "first_name": "Nil",
          "email_address": "nilportugues@localhost",
          "job_title": "Full Stack Web Developer",
          "business_phone": "(123)555-0100",
          "home_phone": "(123)555-0102",
          "mobile_phone": null,
          "fax_number": "(123)555-0103",
          "address": "Plaça Catalunya 1",
          "city": "Barcelona",
          "state_province": "Barcelona",
          "zip_postal_code": "08028",
          "country_region": "Spain",
          "web_page": "http://nilportugues.com",
          "notes": null,
          "attachments": null
       }
  }
}
JSON;
        $this->serverEnvironment('PUT', 'localhost', '/api/v1/employees/1000');
        $response = $this->call('PUT', 'http://localhost/api/v1/employees/1000', json_decode($content, true), [], [], []);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }

    /**
     * @test
     */
    public function testDeleteActionWhenEmployeeDoesNotExistReturns404()
    {
        $this->serverEnvironment('DELETE', 'localhost', '/api/v1/employees/1000');
        $response = $this->call('DELETE', 'http://localhost/api/v1/employees/1000');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('application/vnd.api+json', $response->headers->get('Content-type'));
    }
}
