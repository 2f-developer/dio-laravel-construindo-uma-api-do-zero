<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Busca uma página do catálogo externo e cria ou atualiza produtos e categorias.
 *
 * Roda na fila porque depende de rede: a requisição que pede a importação responde
 * na hora (202) e o worker faz o trabalho, com novas tentativas se a API externa falhar.
 */
class ImportarProdutos implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** Espera crescente entre tentativas, em segundos. */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $limite = 30,
        public int $pular = 0,
    ) {}

    public function handle(): void
    {
        $resposta = Http::timeout(config('catalogo.timeout'))
            ->acceptJson()
            ->get(config('catalogo.url'), [
                'limit' => $this->limite,
                'skip' => $this->pular,
                'select' => 'title,description,price,stock,category,sku,thumbnail',
            ])
            ->throw();

        $itens = collect($resposta->json('products', []))
            ->filter(fn ($item) => filled($item['sku'] ?? null) && filled($item['title'] ?? null));

        DB::transaction(function () use ($itens) {
            foreach ($itens as $item) {
                $nomeCategoria = Str::of($item['category'] ?? 'outros')->replace('-', ' ')->title()->toString();
                $categoria = Category::firstOrCreate(
                    ['slug' => Str::slug($nomeCategoria)],
                    ['name' => $nomeCategoria, 'active' => true],
                );

                // O SKU identifica o produto: importar de novo atualiza em vez de duplicar.
                Product::updateOrCreate(
                    ['sku' => $item['sku']],
                    [
                        'name' => $item['title'],
                        'description' => $item['description'] ?? null,
                        'price' => round((float) $item['price'], 2),
                        'stock' => max(0, (int) ($item['stock'] ?? 0)),
                        'category_id' => $categoria->id,
                        'image_url' => $item['thumbnail'] ?? null,
                        'active' => true,
                    ],
                );
            }
        });

        Log::info('Importação de produtos concluída', ['importados' => $itens->count(), 'pular' => $this->pular]);
    }
}
