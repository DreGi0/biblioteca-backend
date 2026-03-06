<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate([
            'name' => 'librarian',
            'guard_name' => 'api',
        ]);
        Role::firstOrCreate([
            'name' => 'teacher',
            'guard_name' => 'api',
        ]);
        Role::firstOrCreate([
            'name' => 'student',
            'guard_name' => 'api',
        ]);
    }
}
