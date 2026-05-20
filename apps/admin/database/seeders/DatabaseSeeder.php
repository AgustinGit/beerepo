<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'agus@agus.club'],
            ['name' => 'Agus', 'password' => Hash::make('password')],
        );

        Plan::firstOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium',
                'monthly_price' => 1080,
                'cans_per_box' => 6,
                'includes_glass_every_n_months' => 3,
                'shipping_included_zones' => ['montevideo', 'canelones', 'costa'],
                'discount_pct_on_purchases' => 0,
                'active' => true,
            ],
        );

        Plan::firstOrCreate(
            ['slug' => 'basico'],
            [
                'name' => 'Básico',
                'monthly_price' => 350,
                'cans_per_box' => 0,
                'includes_glass_every_n_months' => null,
                'shipping_included_zones' => [],
                'discount_pct_on_purchases' => 29,
                'active' => true,
            ],
        );
    }
}
