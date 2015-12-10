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

use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;
use NilPortugues\Laravel5\JsonApi\Laravel5JsonApiServiceProvider;

/**
 * Class LaravelTestCase.
 */
class LaravelTestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * Setup DB before each test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', ':memory:');

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

        foreach ($fileSystem->files(__DIR__.'/../../../../tests/NilPortugues/Laravel5/JsonApi/Migrations') as $file) {
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

        $app = require __DIR__.'/../../../../vendor/laravel/laravel/bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
        $app->register(\Illuminate\Database\DatabaseServiceProvider::class);
        $app->register(Laravel5JsonApiServiceProvider::class);

        return $app;
    }
}
