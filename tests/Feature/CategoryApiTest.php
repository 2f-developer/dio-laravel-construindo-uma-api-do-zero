<?php

namespace Tests\Feature;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_can_list_categories()
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'active',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_can_create_category()
    {
        $data = [
            'name' => 'Nova Categoria',
            'description' => 'Descrição da nova categoria',
            'active' => true,
        ];

        $response = $this->postJson('/api/categories', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'active',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Nova Categoria',
            'slug' => 'nova-categoria',
        ]);
    }

    public function test_can_show_category()
    {
        $category = Category::first();

        $response = $this->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                ],
            ]);
    }

    public function test_can_update_category()
    {
        $category = Category::first();

        $data = [
            'name' => 'Categoria Atualizada',
        ];

        $response = $this->putJson("/api/categories/{$category->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Categoria Atualizada',
        ]);
    }

    public function test_can_delete_category()
    {
        $category = Category::first();

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_can_get_category_statistics()
    {
        $response = $this->getJson('/api/categories/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_categories',
                    'active_categories',
                    'inactive_categories',
                    'categories_with_products',
                    'categories_without_products',
                ],
            ]);
    }

    public function test_returns_404_for_non_existent_category()
    {
        $response = $this->getJson('/api/categories/99999');

        $response->assertStatus(404);
    }
}
