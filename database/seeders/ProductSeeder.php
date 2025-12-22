<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Facades\File;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = database_path('data/products.csv');
        
        if (!File::exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        $handle = fopen($csvFile, 'r');
        
        // Skip header row
        $header = fgetcsv($handle);
        
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 9) continue;
            
            $data = [
                'name' => $row[0],
                'price' => (float) $row[1],
                'capacity' => $row[2],
                'category' => $row[3],
                'specifications' => json_decode($row[4], true),
                'image_urls' => json_decode($row[5], true) ?: [],
                'rating' => (float) $row[6],
                'review_count' => (int) $row[7],
                'is_active' => $row[8] === 'true',
                'created_by' => 1, // Admin user ID
            ];

            Product::updateOrCreate(
                ['name' => $data['name']],
                $data
            );
            
            $count++;
        }
        
        fclose($handle);
        
        $this->command->info("Imported {$count} products successfully!");
    }
}
