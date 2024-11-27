<?php

namespace Skaisser\LaraSendy\Tests\Feature;

use Skaisser\LaraSendy\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    public function test_it_adds_sent_to_sendy_column_to_users_table()
    {
        $this->assertTrue(Schema::hasColumn('users', 'sent_to_sendy'));
    }

    public function test_it_can_rollback_migration()
    {
        $this->artisan('migrate:rollback')->run();
        $this->assertFalse(Schema::hasTable('users'));
    }

    public function test_it_uses_configured_table_name()
    {
        config(['sendy.target_table' => 'subscribers']);
        
        $this->artisan('migrate:fresh')->run();
        
        $this->assertTrue(Schema::hasColumn('subscribers', 'sent_to_sendy'));
    }
}
