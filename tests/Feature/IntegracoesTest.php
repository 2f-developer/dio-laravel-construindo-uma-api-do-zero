<?php

namespace Tests\Feature;

use App\Jobs\ImportarProdutos;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IntegracoesTest extends TestCase
{
    use RefreshDatabase;

    private function catalogoFalso(array $produtos): void
    {
        Http::fake(['dummyjson.com/*' => Http::response(['products' => $produtos, 'total' => count($produtos)])]);
    }

    public function test_pedir_importacao_enfileira_o_job_e_responde_202(): void
    {
        Queue::fake();

        $this->postJson('/api/importacoes', ['limite' => 10, 'pular' => 20])->assertStatus(202);

        Queue::assertPushed(ImportarProdutos::class, fn ($job) => $job->limite === 10 && $job->pular === 20);
    }

    public function test_parametros_da_importacao_sao_validados(): void
    {
        Queue::fake();

        $this->postJson('/api/importacoes', ['limite' => 500])->assertStatus(422);

        Queue::assertNothingPushed();
    }

    public function test_job_cria_produtos_e_categorias_a_partir_do_catalogo(): void
    {
        $this->catalogoFalso([
            ['title' => 'Mascara', 'description' => 'Cílios', 'price' => 9.99, 'stock' => 99, 'category' => 'beauty', 'sku' => 'BEA-001', 'thumbnail' => 'https://img/1.png'],
            ['title' => 'Notebook', 'price' => 3999.5, 'stock' => 3, 'category' => 'laptops', 'sku' => 'LAP-002'],
            ['title' => 'Sem SKU', 'price' => 1, 'category' => 'beauty'],
        ]);

        (new ImportarProdutos(limite: 3))->handle();

        $this->assertSame(2, Product::count());
        $this->assertEqualsCanonicalizing(['Beauty', 'Laptops'], Category::pluck('name')->all());
        $notebook = Product::where('sku', 'LAP-002')->first();
        $this->assertSame('3999.50', (string) $notebook->price);
        $this->assertSame('Laptops', $notebook->category->name);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'limit=3'));
    }

    public function test_importar_de_novo_atualiza_em_vez_de_duplicar(): void
    {
        Http::fakeSequence('dummyjson.com/*')
            ->push(['products' => [['title' => 'Mascara', 'price' => 9.99, 'stock' => 99, 'category' => 'beauty', 'sku' => 'BEA-001']]])
            ->push(['products' => [['title' => 'Mascara Nova', 'price' => 12.5, 'stock' => 10, 'category' => 'beauty', 'sku' => 'BEA-001']]]);

        (new ImportarProdutos)->handle();
        (new ImportarProdutos)->handle();

        $this->assertSame(1, Product::count());
        $this->assertSame('Mascara Nova', Product::first()->name);
        $this->assertSame(1, Category::count());
    }

    public function test_falha_da_api_externa_faz_o_job_falhar_para_nova_tentativa(): void
    {
        Http::fake(['dummyjson.com/*' => Http::response('erro', 500)]);

        $this->expectException(RequestException::class);
        (new ImportarProdutos)->handle();
    }

    public function test_consulta_cep_no_viacep(): void
    {
        Http::fake(['viacep.com.br/*' => Http::response([
            'cep' => '01001-000', 'logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo', 'uf' => 'SP', 'ibge' => '3550308',
        ])]);

        $this->getJson('/api/cep/01001000')
            ->assertOk()
            ->assertExactJson(['data' => ['cep' => '01001-000', 'logradouro' => 'Praça da Sé', 'bairro' => 'Sé', 'localidade' => 'São Paulo', 'uf' => 'SP']]);
    }

    public function test_cep_inexistente_e_404_e_formato_errado_nem_chega_ao_viacep(): void
    {
        Http::fake(['viacep.com.br/*' => Http::response(['erro' => true])]);

        $this->getJson('/api/cep/99999999')->assertNotFound();
        $this->getJson('/api/cep/123')->assertNotFound();
        Http::assertSentCount(1);
    }

    public function test_viacep_fora_do_ar_e_503(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->getJson('/api/cep/01001000')->assertStatus(503);
    }
}
