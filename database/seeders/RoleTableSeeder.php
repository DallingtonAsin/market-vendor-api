<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create(['name' => 'Vendor', 'is_admin' => 0, 'created_by' => 'System']);
        Role::create(['name' => 'Admin', 'is_admin' => 1, 'created_by' => 'System']);
    }
}