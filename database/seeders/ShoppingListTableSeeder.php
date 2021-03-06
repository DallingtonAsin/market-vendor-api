<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ShoppingListTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\ShoppingOrder::factory()->count(5)->create();
    }
}
