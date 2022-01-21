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
        factory(App\Models\ShoppingList::class, 5)->create();
    }
}
