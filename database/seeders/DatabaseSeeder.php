<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Admin;
use App\Models\Buyer;
use App\Models\Merchant;
use App\Models\ProductType;
use Illuminate\Database\Seeder;
use Webpatser\Uuid\Uuid;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        ProductType::create([
            'name'=>'Food'
        ]);

        ProductType::create([
            'name'=>'Baverage'
        ]);

        ProductType::create([
            'name'=>'Snack'
        ]);

        Admin::create([
            'username' => 'bimeal',
            'password' => bcrypt('merdeka1945'),
            'email' => 'admin@bimeal.com',
            'phone' => '085158221902',
            'name' => 'BiMeal'
        ]);

        Buyer::create([
            'username' => 'buyer',
            'password' => bcrypt('buyer1945'),
            'email' => 'buyer@bimeal.com',
            'phone' => '08123123123',
            'name' => 'Buyer Example',
            'group' => 'STUDENT',
            'group_id' => '25029312803',
        ]);

        Merchant::create([
            'username' => 'merchant',
            'password' => bcrypt('merchant1945'),
            'email' => 'merchant@bimeal.com',
            'phone' => '088172368172',
            'name' => 'Merchant Good',
            'location_number' => '001',
            'time_open' => '10:00',
            'time_close' => '16:00'
        ]);
    }
}
