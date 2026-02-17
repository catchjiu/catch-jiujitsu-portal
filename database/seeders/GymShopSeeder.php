<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;

class GymShopSeeder extends Seeder
{
    /**
     * Seed the Gym Shop with sample products and variants.
     * Run after migration: php artisan db:seed --class=GymShopSeeder
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Catch BJJ Gi',
                'category' => 'Gi',
                'description' => 'Premium BJJ gi with Catch Jiu Jitsu branding.',
                'price' => 3500,
                'variants' => [
                    ['size' => 'A1', 'color' => 'White', 'stock_quantity' => 5],
                    ['size' => 'A2', 'color' => 'White', 'stock_quantity' => 8],
                    ['size' => 'M', 'color' => 'White', 'stock_quantity' => 2],
                    ['size' => 'L', 'color' => 'Blue', 'stock_quantity' => 4],
                ],
            ],
            [
                'name' => 'Rank Belt',
                'category' => 'Belt',
                'description' => 'IBJJF-approved BJJ rank belt.',
                'price' => 450,
                'variants' => [
                    ['size' => '1', 'color' => 'White', 'stock_quantity' => 10],
                    ['size' => '2', 'color' => 'Blue', 'stock_quantity' => 6],
                    ['size' => '3', 'color' => 'Purple', 'stock_quantity' => 4],
                    ['size' => '4', 'color' => 'Brown', 'stock_quantity' => 2],
                    ['size' => '5', 'color' => 'Black', 'stock_quantity' => 1],
                ],
            ],
            [
                'name' => 'Catch Rash Guard',
                'category' => 'Rash guard',
                'description' => 'Long-sleeve rash guard with gym logo.',
                'price' => 1200,
                'variants' => [
                    ['size' => 'S', 'color' => 'Black', 'stock_quantity' => 5],
                    ['size' => 'M', 'color' => 'Black', 'stock_quantity' => 7],
                    ['size' => 'L', 'color' => 'Navy', 'stock_quantity' => 2],
                ],
            ],
            [
                'name' => 'Spats / Shorts',
                'category' => 'Shorts',
                'description' => 'Comfortable training shorts.',
                'price' => 800,
                'variants' => [
                    ['size' => 'S', 'color' => 'Black', 'stock_quantity' => 4],
                    ['size' => 'M', 'color' => 'Black', 'stock_quantity' => 6],
                    ['size' => 'L', 'color' => 'Grey', 'stock_quantity' => 3],
                ],
            ],
            [
                'name' => 'Catch T-shirt',
                'category' => 'T-shirt',
                'description' => 'Cotton gym T-shirt with Catch branding.',
                'price' => 550,
                'variants' => [
                    ['size' => 'S', 'color' => null, 'stock_quantity' => 10],
                    ['size' => 'M', 'color' => null, 'stock_quantity' => 12],
                    ['size' => 'L', 'color' => null, 'stock_quantity' => 8],
                ],
            ],
            [
                'name' => 'Catch Sticker Pack',
                'category' => 'Sticker',
                'description' => 'Set of 5 Catch Jiu Jitsu stickers.',
                'price' => 100,
                'variants' => [
                    ['size' => 'One size', 'color' => null, 'stock_quantity' => 50],
                ],
            ],
        ];

        foreach ($products as $data) {
            $variants = $data['variants'];
            unset($data['variants']);
            $product = Product::create($data);
            foreach ($variants as $v) {
                $product->variants()->create($v);
            }
        }
    }
}
