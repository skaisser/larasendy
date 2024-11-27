<?php

declare(strict_types=1);

namespace Skaisser\LaraSendy\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Skaisser\LaraSendy\SendyServiceProvider;
use Skaisser\LaraSendy\Http\Clients\SendyClient;
use Skaisser\LaraSendy\Tests\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create users table
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });

        $this->setUpConfig();

        Log::info('Debug - Test setup complete. SendyClient configured with mock client.');
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('subscribers');
        parent::tearDown();
    }

    protected function getPackageProviders($app)
    {
        return [
            SendyServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Use array cache driver for testing
        $app['config']->set('cache.default', 'array');
        $app['config']->set('cache.stores.array', [
            'driver' => 'array',
            'serialize' => false,
        ]);

        // Database configuration
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Sendy configuration
        $app['config']->set('sendy.installation_url', 'http://test.sendy.local');
        $app['config']->set('sendy.api_key', 'test_api_key');
        $app['config']->set('sendy.list_id', 'test_list_id');
        $app['config']->set('sendy.default_model', \Skaisser\LaraSendy\Tests\Models\User::class);
        $app['config']->set('sendy.fields_mapping', [
            'email' => 'email',
            'name' => 'name',
            'company' => 'company',
            'country' => 'country'
        ]);
    }

    protected function setUpConfig()
    {
        config([
            'sendy.installation_url' => 'http://test.sendy.local',
            'sendy.api_key' => 'test_api_key',
            'sendy.list_id' => 'test_list_id',
            'sendy.default_model' => \Skaisser\LaraSendy\Tests\Models\User::class,
            'sendy.fields_mapping' => [
                'email' => 'email',
                'name' => 'name',
                'company' => 'company',
                'country' => 'country'
            ]
        ]);
    }
}
