<?php

namespace Skaisser\LaraSendy\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Skaisser\LaraSendy\SendyServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->artisan('migrate:fresh')->run();
    }

    protected function getPackageProviders($app)
    {
        return [
            SendyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('sendy', [
            'url' => 'http://sendy.test',
            'api_key' => 'test-api-key',
            'list_id' => 'test-list-id',
            'target_table' => 'users',
            'sync_interval' => 60,
            'fields_mapping' => [
                'email' => 'email',
                'name' => 'name',
                'company' => 'company_name',
            ],
            'gdpr' => false,
            'silent' => true,
            'referrer' => 'http://localhost',
            'honeypot' => false,
        ]);
    }
}
