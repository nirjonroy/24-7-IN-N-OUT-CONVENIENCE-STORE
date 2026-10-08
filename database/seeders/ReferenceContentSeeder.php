<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ReferenceContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RepairReferenceSeeder::class,
            PhoneAccessoryReferenceSeeder::class,
        ]);
    }
}
