# Laravel 5 JSON API Transformer Package


[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/laravel5-jsonapi-transformer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/laravel5-jsonapi-transformer/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/22db88f5-d061-4b32-bad1-4b806ac07318/mini.png)](https://insight.sensiolabs.com/projects/22db88f5-d061-4b32-bad1-4b806ac07318) 
[![Latest Stable Version](https://poser.pugx.org/nilportugues/laravel5-json-api/v/stable)](https://packagist.org/packages/nilportugues/laravel5-json-api) 
[![Total Downloads](https://poser.pugx.org/nilportugues/laravel5-json-api/downloads)](https://packagist.org/packages/nilportugues/laravel5-json-api) 
[![License](https://poser.pugx.org/nilportugues/laravel5-json-api/license)](https://packagist.org/packages/nilportugues/laravel5-json-api) 



## Installation

Use [Composer](https://getcomposer.org) to install the package:

```
$ composer require nilportugues/laravel5-json-api
```


## Laravel 5 / Lumen Configuration

**Step 1: Add the Service Provider**

**Laravel**

Open up `config/app.php` and add the following line under `providers` array:

```php
'providers' => [

    //...
    NilPortugues\Laravel5\JsonApiSerializer\Laravel5JsonApiServiceProvider::class,
],
```

**Lumen**

Open up `bootstrap/app.php`and add the following lines before the `return $app;` statement:

```php
$app->register(\NilPortugues\Laravel5\JsonApiSerializer\Laravel5JsonApiServiceProvider::class);
$app->configure('jsonapi');
```

Also, enable Facades by uncommenting:

```php
$app->withFacades();
```


**Step 2: Add the mapping**

Create a `jsonapi.php` file in `config/` directory. This file should return an array returning all the class mappings.


**Step 3: Usage**

For instance, lets say the following object has been fetched from a Repository , lets say `\App\Models\User` - this being implemented in **Eloquent**, but can be anything.

This is its migration file:

```php
<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration 
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('username', 30)->unique();
            $table->string('email')->unique();
            $table->string('password', 60);
            $table->integer('role_id')->unsigned();
            $table->boolean('seen')->default(false);
            $table->boolean('valid')->default(false);
            $table->boolean('confirmed')->default(false);
            $table->string('confirmation_code')->nullable();
            $table->timestamps();
            $table->rememberToken();            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('users');
    }

}
```

And a series of mappings, placed in `config/jsonapi.php`, that require to use *named routes* so we can use the `route()` helper function:

```php
<?php
//config/jsonapi.php
return [
    [
        'class' => '\App\Models\User',
        'alias' => 'User',
        'aliased_properties' => [
            'created_at' => 'registered_on',
        ],
        'hide_properties' => [
            'password', 
            'role_id',
            'seen', 
            'valid', 
            'confirmed', 
            'confirmation_code', 
            'remember_token',
            'updated_at'

        ],
        'id_properties' => [
            'id',
        ],
        'urls' => [
            'self' => 'get_user', //named route
        ],
        // (Optional)
        // 'relationships' => [
        //     'author' => [
        //         'related' => 'get_user_friends', //named route
        //         'self' => 'get_user_friends_relationship', //named route
        //     ]
        // ],
    ],
];

```

The named routes belong to the `app/Http/routes.php`. Here's a sample for the routes provided mapping:

**Laravel**

```php
Route::get(
  '/user/{id}',
  ['as' => 'get_user', 'uses' => 'UserController@getOneUserAction']
);

Route::get(
  '/user',
  ['as' => 'get_users', 'uses' => 'UserController@getAllUsersAction']
);

//...
```

**Lumen**

```php
$app->get(
  '/user/{id}',
  ['as' => 'get_user', 'uses' => 'UserController@getOneUserAction']
);

$app->get(
  '/user',
  ['as' => 'get_users', 'uses' => 'UserController@getAllUsersAction']
);

//...
``` 

All of this set up allows you to easily use the `JsonApiSerializer` service as follows:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use NilPortugues\Laravel5\JsonApiSerializer\JsonApiSerializer;
use NilPortugues\Laravel5\JsonApiSerializer\ResponseTrait;

/**
 * Laravel Controller example
 */
class UserController extends \App\Http\Controllers\Controller
{
    use ResponseTrait;
    
    /**
     * @var App\Models\User
     */
    private $userRepository;

    /**
     * @var JsonApiSerializer
     */
    private $serializer;

    /**
     * @param User $userRepository
     * @param JsonApiSerializer $jsonApiSerializer
     */
    public function __construct(User $userRepository, JsonApiSerializer $jsonApiSerializer)
    {
        $this->userRepository = $userRepository;
        $this->serializer = $jsonApiSerializer;
    }

    /**
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOneUserAction($id)
    {
        $user = $this->userRepository->find($id);
        
        /** @var \NilPortugues\Api\JsonApi\JsonApiTransformer $transformer */
        $transformer = $this->serializer->getTransformer();
        $transformer->setSelfUrl(route('get_user', ['id' => $id]));
        $transformer->setNextUrl(route('get_user', ['id' => $id+1]));

        return $this->response($this->serializer->serialize($user));
    }
    
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllUsersAction()
    {
        return $this->response($this->serializer->serialize($this->userRepository->all()));
    }
}
```


**Output:**

This is the output for `UserController@getAllUsersAction` method:

```
HTTP/1.1 200 OK
Cache-Control: private, max-age=0, must-revalidate
Content-type: application/vnd.api+json
```

```json
{
    "data": [
        {
            "type": "user",
            "id": "1",
            "attributes": {
                "username": "Admin",
                "email": "admin@example.com",
                "registered_on": "2015-09-07 18:02:18"
            },
            "links": {
                "self": {
                    "href": "http://localhost:8000/user/1"
                }
            }
        },
        {
            "type": "user",
            "id": "2",
            "attributes": {
                "username": "Redactor",
                "email": "redac@example.com",
                "registered_on": "2015-09-07 18:02:18"
            },
            "links": {
                "self": {
                    "href": "http://localhost:8000/user/2"
                }
            }
        },
        {
            "type": "user",
            "id": "3",
            "attributes": {
                "username": "Walker",
                "email": "walker@example.com",
                "registered_on": "2015-09-07 18:02:18"
            },
            "links": {
                "self": {
                    "href": "http://localhost:8000/user/3"
                }
            }
        },
    ],
    "jsonapi": {
        "version": "1.0"
    }
}
```

#### Request objects

JSON API comes with a helper Request class, `NilPortugues\Api\JsonApi\Http\Message\Request(ServerRequestInterface $request)`, implementing the PSR-7 Request Interface. Using this request object will provide you access to all the interactions expected in a JSON API:

##### JSON API Query Parameters:

- &filter[resource]=field1,field2
- &include[resource]
- &include[resource.field1]
- &sort=field1,-field2
- &sort=-field1,field2
- &page[number]
- &page[limit]
- &page[cursor]
- &page[offset]
- &page[size]


##### NilPortugues\Api\JsonApi\Http\Message\Request

Given the query parameters listed above, Request implements helper methods that parse and return data already prepared.

```php
namespace NilPortugues\Api\JsonApi\Http\Message;

final class Request
{
    public function __construct(ServerRequestInterface $request) { ... }
    public function getQueryParam($name, $default = null) { ... }
    public function getIncludedRelationships($baseRelationshipPath) { ... }
    public function getSortFields() { ... }
    public function getAttribute($name, $default = null) { ... }
    public function getSortDirection() { ... }
    public function getPageNumber() { ... }
    public function getPageLimit() { ... }
    public function getPageOffset() { ... }
    public function getPageSize() { ... }
    public function getPageCursor() { ... }
    public function getFilters() { ... }
}
```

#### Response objects (ResponseTrait)

The following `ResponseTrait` methods are provided to return the right headers and HTTP status codes are available:

```php
    private function errorResponse($json);
    private function resourceCreatedResponse($json);
    private function resourceDeletedResponse($json);
    private function resourceNotFoundResponse($json);
    private function resourcePatchErrorResponse($json);
    private function resourcePostErrorResponse($json);
    private function resourceProcessingResponse($json);
    private function resourceUpdatedResponse($json);
    private function response($json);
    private function unsupportedActionResponse($json);
```    


## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/laravel5-jsonapi-transformer/pulls).


<br>
## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/laravel5-jsonapi-transformer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/laravel5-jsonapi-transformer).


<br>
## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/laravel5-jsonapi-transformer/issues/new)
 - Using Gitter: [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/nilportugues/laravel5-jsonapi-transformer?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)


<br>
## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/laravel5-jsonapi-transformer/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
