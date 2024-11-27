<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Skaisser\LaraSendy\Tests\TestCase;

class MigrationTest extends TestCase
{
    /** @test */
    public function it_adds_sent_to_sendy_column_to_users_table()
    {
        $this->assertTrue(Schema::hasColumn('users', 'sent_to_sendy'));
        
        $column = Schema::getConnection()
            ->getDoctrineColumn('users', 'sent_to_sendy');
        
        $this->assertEquals('boolean', $column->getType()->getName());
        $this->assertFalse($column->getDefault());
    }

    /** @test */
    public function it_can_rollback_migration()
    {
        // Rollback the migration
        $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
        
        $this->assertFalse(Schema::hasColumn('users', 'sent_to_sendy'));
    }

    /** @test */
    public function it_uses_configured_table_name()
    {
        // Change the target table in config
        config(['sendy.target_table' => 'subscribers']);

        // Create the subscribers table
        Schema::create('subscribers', function ($table) {
            $table->id();
            $table->string('email');
            $table->timestamps();
        });

        // Fresh migration with new config
        $this->artisan('migrate:fresh', ['--database' => 'testbench'])->run();

        $this->assertTrue(Schema::hasColumn('subscribers', 'sent_to_sendy'));
    }
}
