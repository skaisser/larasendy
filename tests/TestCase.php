<?php

namespace Skaisser\LaraSendy\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Skaisser\LaraSendy\SendyServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SendyServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Setup Sendy configuration for testing
        $app['config']->set('sendy', [
            'url' => 'https://test-sendy-url.com',
            'api_key' => 'test-api-key',
            'list_id' => 'test-list-id',
            'target_table' => 'users',
            'sync_interval' => 60,
            'fields_mapping' => [
                'email' => 'email',
                'name' => 'name',
                'company' => 'company_name',
            ],
        ]);
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');
        
        // Create a test users table
        $this->artisan('migrate', ['--database' => 'testbench'])->run();
        
        \Illuminate\Support\Facades\Schema::create('users', function ($table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('company_name')->nullable();
            $table->timestamps();
        });
    }
}
