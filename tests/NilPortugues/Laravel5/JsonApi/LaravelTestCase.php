<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/9/15
 * Time: 5:24 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Tests\Laravel5\JsonApi;

use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Routing\Router;
use NilPortugues\Tests\App\Transformers\EmployeesTransformer;
use NilPortugues\Tests\App\Transformers\OrdersTransformer;

/**
 * Class LaravelTestCase.
 */
class LaravelTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use WithoutMiddleware;
    /**
     * Setup DB before each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');
        $this->app['config']->set('jsonapi', [EmployeesTransformer::class, OrdersTransformer::class]);
        $this->app['config']->set('app.url', 'http://example.com/');
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('app.key', \env('APP_KEY', '1234567890123456'));
        $this->app['config']->set('app.cipher', 'AES-128-CBC');

        $this->app->boot();

        $this->migrate();
    }

    /**
     * run package database migrations.
     */
    public function migrate()
    {
        $fileSystem = new Filesystem();
        $classFinder = new ClassFinder();

        foreach ($fileSystem->files(__DIR__.'/../../../../tests/NilPortugues/App/Migrations') as $file) {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);
            (new $migrationClass())->down();
            (new $migrationClass())->up();
        }
    }

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        /** @var $app \Illuminate\Foundation\Application */
        $app = require __DIR__.'/../../../../vendor/laravel/laravel/bootstrap/app.php';

        $this->setUpHttpKernel($app);
        $app->register(\Illuminate\Database\DatabaseServiceProvider::class);
        $app->register(\NilPortugues\Tests\App\Providers\RouteServiceProvider::class);
        $app->register(\NilPortugues\Laravel5\JsonApi\Laravel5JsonApiServiceProvider::class);

        return $app;
    }

    /**
     * @return Router
     */
    protected function getRouter()
    {
        $router = new Router(new Dispatcher());

        return $router;
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    private function setUpHttpKernel($app)
    {
        $app->instance('request', \Illuminate\Http\Request::capture());
        $app->make('Illuminate\Foundation\Http\Kernel', [$app, $this->getRouter()])->bootstrap();
    }
}
