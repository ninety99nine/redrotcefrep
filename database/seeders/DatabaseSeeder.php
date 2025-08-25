<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AiTopicSeeder::class,
            CourierSeeder::class,
            PricingPlanSeeder::class,
            PaymentMethodSeeder::class,
        ]);
    }
}
