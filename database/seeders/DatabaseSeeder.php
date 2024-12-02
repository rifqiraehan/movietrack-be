<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\WatchList;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        File::deleteDirectory(public_path('storage/app/public/pfps'));
        File::deleteDirectory(storage_path('app/public/pfps'));

        $this->call(UserSeeder::class);
        $this->call(MovieSeeder::class);
        $this->call(GenreSeeder::class);
        $this->call(StatusSeeder::class);
        $this->call(ReviewSeeder::class);
        $this->call(WatchlistSeeder::class);
    }
}
