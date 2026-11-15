<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Eletrônicos',
                'slug' => 'eletronicos',
                'description' => 'Dispositivos eletrônicos e gadgets',
                'active' => true,
            ],
            [
                'name' => 'Informática',
                'slug' => 'informatica',
                'description' => 'Computadores, laptops e acessórios',
                'active' => true,
            ],
            [
                'name' => 'Games',
                'slug' => 'games',
                'description' => 'Jogos, consoles e acessórios gamer',
                'active' => true,
            ],
            [
                'name' => 'Audio',
                'slug' => 'audio',
                'description' => 'Fones de ouvido, caixas de som e áudio profissional',
                'active' => true,
            ],
            [
                'name' => 'Smart Home',
                'slug' => 'smart-home',
                'description' => 'Dispositivos inteligentes para casa',
                'active' => false,
            ],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
