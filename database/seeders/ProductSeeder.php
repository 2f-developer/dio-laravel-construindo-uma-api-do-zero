<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Smartphone Galaxy A54',
                'description' => 'Smartphone Android com 128GB de armazenamento, 6GB RAM, câmera 50MP',
                'price' => 1899.90,
                'stock' => 25,
                'sku' => 'SM-GALAXY-A54-128',
                'category' => 'eletronicos',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Laptop Dell Inspiron 15',
                'description' => 'Laptop Intel Core i5, 16GB RAM, SSD 512GB, tela 15.6" Full HD',
                'price' => 3599.00,
                'stock' => 10,
                'sku' => 'LAP-DEL-INSP15-512',
                'category' => 'informatica',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Fone de Ouvido Sony WH-1000XM5',
                'description' => 'Headphone Bluetooth com cancelamento de ruído ativo, 30h de bateria',
                'price' => 2499.90,
                'stock' => 50,
                'sku' => 'FON-SONY-XM5',
                'category' => 'audio',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'PlayStation 5 Slim',
                'description' => 'Console PlayStation 5 Slim 1TB, com controle DualSense',
                'price' => 3999.90,
                'stock' => 15,
                'sku' => 'PS5-SLIM-1TB',
                'category' => 'games',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Mouse Gamer Logitech G502',
                'description' => 'Mouse gamer com sensor HERO 25K DPI, 11 botões programáveis',
                'price' => 299.90,
                'stock' => 100,
                'sku' => 'MOU-LOGI-G502',
                'category' => 'games',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Teclado Mecânico Redragon',
                'description' => 'Teclado mecânico RGB, switches blue, ABNT2',
                'price' => 249.90,
                'stock' => 75,
                'sku' => 'TEC-RED-K552',
                'category' => 'games',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Monitor LG UltraGear 24"',
                'description' => 'Monitor gaming 24" Full HD 144Hz, 1ms, IPS',
                'price' => 1299.90,
                'stock' => 20,
                'sku' => 'MON-LG-24GN60R',
                'category' => 'informatica',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Caixa de Som JBL Flip 6',
                'description' => 'Caixa de som Bluetooth portátil, à prova d\'água, 12h bateria',
                'price' => 649.90,
                'stock' => 0,
                'sku' => 'AUD-JBL-FLIP6',
                'category' => 'audio',
                'active' => true,
                'image_url' => null,
            ],
            [
                'name' => 'Alexa Echo Dot 5ª Geração',
                'description' => 'Smart speaker com Alexa, som de alta definição',
                'price' => 399.90,
                'stock' => 30,
                'sku' => 'SMH-ECHO-DOT5',
                'category' => 'smart-home',
                'active' => false,
                'image_url' => null,
            ],
            [
                'name' => 'SSD Kingston 500GB',
                'description' => 'SSD NVMe M.2 500GB, 3500MB/s leitura, 2100MB/s escrita',
                'price' => 349.90,
                'stock' => 40,
                'sku' => 'INF-KING-SNV3S',
                'category' => 'informatica',
                'active' => true,
                'image_url' => null,
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('slug', $productData['category'])->first();

            Product::firstOrCreate(
                ['sku' => $productData['sku']],
                [
                    'name' => $productData['name'],
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'sku' => $productData['sku'],
                    'category_id' => $category?->id,
                    'active' => $productData['active'],
                    'image_url' => $productData['image_url'],
                ]
            );
        }
    }
}
