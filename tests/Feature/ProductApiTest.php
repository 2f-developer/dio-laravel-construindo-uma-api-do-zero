<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        $this->seed(CategorySeeder::class);
        $this->seed(ProductSeeder::class);
    }

    public function test_can_list_products()
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock',
                        'sku',
                        'category_id',
                        'active',
                        'image_url',
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

    public function test_can_create_product()
    {
        $category = Category::first();

        $data = [
            'name' => 'Produto de Teste',
            'description' => 'Descrição do produto de teste',
            'price' => 99.90,
            'stock' => 10,
            'sku' => 'TEST-001',
            'category_id' => $category->id,
            'active' => true,
        ];

        $response = $this->postJson('/api/products', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'stock',
                    'sku',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Produto de Teste',
            'sku' => 'TEST-001',
        ]);
    }

    public function test_validation_fails_without_required_fields()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price', 'stock', 'sku']);
    }

    public function test_can_show_product()
    {
        $product = Product::first();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                    'stock',
                    'sku',
                ],
            ]);
    }

    public function test_can_update_product()
    {
        $product = Product::first();

        $data = [
            'name' => 'Produto Atualizado',
            'price' => 149.90,
        ];

        $response = $this->putJson("/api/products/{$product->id}", $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Produto Atualizado',
        ]);
    }

    public function test_can_delete_product()
    {
        $product = Product::first();

        $response = $this->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_can_filter_products_by_category()
    {
        $category = Category::first();

        $response = $this->getJson("/api/products?category_id={$category->id}");

        $response->assertStatus(200);

        $data = $response->json('data');
        foreach ($data as $product) {
            $this->assertEquals($category->id, $product['category_id']);
        }
    }

    public function test_can_search_products()
    {
        $response = $this->getJson('/api/products?search=Smartphone');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_can_update_product_stock()
    {
        $product = Product::first();

        $response = $this->patchJson("/api/products/{$product->id}/stock", [
            'stock' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'stock',
                ],
                'message',
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 50,
        ]);
    }

    public function test_can_get_product_statistics()
    {
        $response = $this->getJson('/api/products/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_products',
                    'active_products',
                    'inactive_products',
                    'products_in_stock',
                    'products_out_of_stock',
                    'total_stock_value',
                    'average_price',
                    'min_price',
                    'max_price',
                ],
            ]);
    }

    public function test_returns_404_for_non_existent_product()
    {
        $response = $this->getJson('/api/products/99999');

        $response->assertStatus(404);
    }
}
