<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DrugSeeder::class,
            RuleDefinitionSeeder::class,
        ]);

        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',  // Mutator hashes it; bcrypt would be ignored
            ]
        );

        $this->call([HealthDataSeeder::class]);
    }
}
