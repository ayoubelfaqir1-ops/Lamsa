<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (str_contains(static::class, '\\Feature\\') && Schema::hasTable('roles')) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $this->seed(RolePermissionSeeder::class);
        }
    }
}
