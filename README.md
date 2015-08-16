# Laravel 5 JSON API Transformer Package


[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nilportugues/laravel5-json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nilportugues/json-api/?branch=master) [![SensioLabsInsight](https://insight.sensiolabs.com/projects/e39e4c0e-a402-495b-a763-6e0482e2083d/mini.png)](https://insight.sensiolabs.com/projects/e39e4c0e-a402-495b-a763-6e0482e2083d) 
[![Latest Stable Version](https://poser.pugx.org/nilportugues/laravel5-json-api/v/stable)](https://packagist.org/packages/nilportugues/json-api) 
[![Total Downloads](https://poser.pugx.org/nilportugues/laravel5-json-api/downloads)](https://packagist.org/packages/nilportugues/json-api) 
[![License](https://poser.pugx.org/nilportugues/laravel5-json-api/license)](https://packagist.org/packages/nilportugues/json-api) 



## Installation

Use [Composer](https://getcomposer.org) to install the package:

```
$ composer require nilportugues/laravel5-json-api
```

**Recommendation**

Due to the lack of current support for PSR-7 Requests and Responses, it is also recommended to install the package `symfony/psr-http-message-bridge`that will bridge between the PHP standard and the Response object used by Laravel.


## Laravel 5 / Lumen Configuration

**Step 1: Add the Service Provider**
Open up `bootstrap/app.php`and add the following lines before the `return $app;` statement:

```php
$app->register('NilPortugues\Laravel5\JsonApiSerializer\Laravel5JsonApiSerializerServiceProvider');
$app['config']->set('jsonapi_mapping', include('jsonapi.php'));
```

**Step 2: Add the mapping**

Create a `jsonapi.php` file in `bootstrap/` directory. This file should return an array returning all the class mappings.

An example as follows:


**Step 3: Usage**

For instance, lets say the following object has been fetched from a Repository , lets say `PostRepository` - this being implemented in Eloquent or whatever your flavour is:

```php
use Acme\Domain\Dummy\Post;
use Acme\Domain\Dummy\ValueObject\PostId;
use Acme\Domain\Dummy\User;
use Acme\Domain\Dummy\ValueObject\UserId;
use Acme\Domain\Dummy\Comment;
use Acme\Domain\Dummy\ValueObject\CommentId;

//$postId = 9;
//PostRepository::findById($postId); 

$post = new Post(
  new PostId(9),
  'Hello World',
  'Your first post',
  new User(
      new UserId(1),
      'Post Author'
  ),
  [
      new Comment(
          new CommentId(1000),
          'Have no fear, sers, your king is safe.',
          new User(new UserId(2), 'Barristan Selmy'),
          [
              'created_at' => (new \DateTime('2015/07/18 12:13:00'))->format('c'),
              'accepted_at' => (new \DateTime('2015/07/19 00:00:00'))->format('c'),
          ]
      ),
  ]
);
```

And a series of mappings, placed in `bootstrap/jsonapi.php`, that require to use *named routes* so we can use the `route()` helper function:

```php
<?php
//bootstrap/jsonapi.php
return [
    [
        'class' => 'Acme\Domain\Dummy\Post',
        'alias' => 'Message',
        'aliased_properties' => [
            'author' => 'author',
            'title' => 'headline',
            'content' => 'body',
        ],
        'hide_properties' => [

        ],
        'id_properties' => [
            'postId',
        ],
        'urls' => [
            'self' => route('get_post'),
            'comments' => route('get_post_comments'),
        ],
        // (Optional)
        'relationships' => [
            'author' => [
                'related' => 'self' => route('get_post_author'),
                'self' => 'self' => route('get_post_author_relationship'),
            ]
        ],
    ],
    [
        'class' => 'Acme\Domain\Dummy\ValueObject\PostId',
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'postId',
        ],
        'urls' => [
            'self' => 'self' => route('get_post'),
            'relationships' => [
                'comment' => route('get_comment_author_relationship'),
            ],
        ],
    ],
    [
        'class' => 'Acme\Domain\Dummy\User',
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'userId',
        ],
        'urls' => [
            'self' => route('get_user'),
            'friends' => route('get_user_friends'),
            'comments' => route('get_user_comments'),
        ],
    ],
    [
        'class' => 'Acme\Domain\Dummy\ValueObject\UserId',
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'userId',
        ],
        'urls' => [
            'self' => route('get_user'),
            'friends' => route('get_user_friends'),
            'comments' => route('get_user_comments'),
        ],
    ],
    [
        'class' => 'Acme\Domain\Dummy\Comment',
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'commentId',
        ],
        'urls' => [
            'self' => route('get_comment'),
        ],
        'relationships' => [
            'post' => [
                'self' => route('get_post_comments_relationship'),
            ]
        ],
    ],
    [
        'class' => 'Acme\Domain\Dummy\ValueObject\CommentId',
        'alias' => '',
        'aliased_properties' => [],
        'hide_properties' => [],
        'id_properties' => [
            'commentId',
        ],
        'urls' => [
            'self' => route('get_comment'),
        ],
        'relationships' => [
            'post' => [
                'self' => route('get_post_comments_relationship'),
            ]
        ],
    ],
];

```

The named routes belong to the `app/Http/routes.php`. Here's a sample for the routes provided mapping:

```php
$app->get(
  '/post/{postId}',
  ['as' => 'get_post', 'uses' => 'PostController@getPostAction']
);

$app->get(
  '/post/{postId}/comments',
  ['as' => 'get_post_comments', 'uses' => 'CommentsController@getPostCommentsAction']
);

//...
``` 

All of this set up allows you to easily use the `JsonApiSerializer` service as follows:

```php
<?php

namespace App\Http\Controllers;

use Acme\Domain\Dummy\PostRepository;
use NilPortugues\Api\JsonApi\Http\Message\Response;
use NilPortugues\Serializer\Serializer;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;


class PostController extends \Laravel\Lumen\Routing\Controller
{
    /**
     * @var PostRepository
     */
    private $postRepository;

    /**
     * @param PostRepository $postRepository
     * @param Serializer $jsonApiSerializer
     */
    public function __construct(PostRepository $postRepository, Serializer $jsonApiSerializer)
    {
        $this->postRepository = $postRepository;
        $this->jsonApiSerializer = $jsonApiSerializer;
    }

    /**
     * @param int $postId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPostAction($postId)
    {
        $post = $this->postRepository->findById($postId);
        $json = $this->jsonApiSerializer->serialize($post);

        return (new HttpFoundationFactory())->createResponse(new Response($json));
    }
}
```


## Quality

To run the PHPUnit tests at the command line, go to the tests directory and issue phpunit.

This library attempts to comply with [PSR-1](http://www.php-fig.org/psr/psr-1/), [PSR-2](http://www.php-fig.org/psr/psr-2/), [PSR-4](http://www.php-fig.org/psr/psr-4/) and [PSR-7](http://www.php-fig.org/psr/psr-7/).

If you notice compliance oversights, please send a patch via [Pull Request](https://github.com/nilportugues/laravel5-jsonapi-transformer/pulls).



## Contribute

Contributions to the package are always welcome!

* Report any bugs or issues you find on the [issue tracker](https://github.com/nilportugues/laravel5-jsonapi-transformer/issues/new).
* You can grab the source code at the package's [Git repository](https://github.com/nilportugues/laravel5-jsonapi-transformer).



## Support

Get in touch with me using one of the following means:

 - Emailing me at <contact@nilportugues.com>
 - Opening an [Issue](https://github.com/nilportugues/laravel5-jsonapi-transformer/issues/new)
 - Using Gitter: [![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/nilportugues/laravel5-jsonapi-transformer?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge)



## Authors

* [Nil Portugués Calderó](http://nilportugues.com)
* [The Community Contributors](https://github.com/nilportugues/laravel5-jsonapi-transformer/graphs/contributors)


## License
The code base is licensed under the [MIT license](LICENSE).
