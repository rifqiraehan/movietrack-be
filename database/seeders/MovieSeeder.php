<?php

namespace Database\Seeders;

use App\Models\Movie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ids = [
            333623, 10494, 257475, 8392, 615165, 257475, 615165,
            747, 8699, 7446, 120467, 546554, 385687, 447365, 985617, 980489, 974635,
            713704, 116104, 811704, 113443, 976785, 758323, 507089, 768362, 955531, 977177
        ];

        // just create the movie id in Movie table
        foreach ($ids as $id) {
            Movie::create([
                'id' => $id
            ]);
        }
    }
}