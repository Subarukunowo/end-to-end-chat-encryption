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
        $budi = User::factory()->create([
            'name' => 'Budi Santoso',
            'email' => 'budi@chat.com',
            'password' => bcrypt('password123'),
            'avatar' => 'https://ui-avatars.com/api/?name=Budi+Santoso&background=random',
        ]);

        $siti = User::factory()->create([
            'name' => 'Siti Aminah',
            'email' => 'siti@chat.com',
            'password' => bcrypt('password456'),
            'avatar' => 'https://ui-avatars.com/api/?name=Siti+Aminah&background=random',
        ]);

        // Saling save kontak
        $budi->contacts()->attach($siti->id);
        $siti->contacts()->attach($budi->id);
    }
}
