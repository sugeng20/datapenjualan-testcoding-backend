<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Type;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            ['name' => 'Kopi', 'stock' => 100, 'type_id' => Type::where('type', 'Konsumsi')->first()->id],
            ['name' => 'Teh', 'stock' => 100, 'type_id' => Type::where('type', 'Konsumsi')->first()->id],
            ['name' => 'Pasta Gigi', 'stock' => 100, 'type_id' => Type::where('type', 'Pembersih')->first()->id],
            ['name' => 'Sabun Mandi', 'stock' => 100, 'type_id' => Type::where('type', 'Pembersih')->first()->id],
            ['name' => 'Sampo', 'stock' => 100, 'type_id' => Type::where('type', 'Pembersih')->first()->id],
        ];

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
