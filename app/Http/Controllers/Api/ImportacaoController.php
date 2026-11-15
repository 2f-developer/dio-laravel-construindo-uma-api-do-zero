<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ImportarProdutos;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImportacaoController extends Controller
{
    /** Enfileira a importação de produtos do catálogo externo. */
    public function store(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'limite' => ['sometimes', 'integer', 'between:1,100'],
            'pular' => ['sometimes', 'integer', 'min:0'],
        ]);

        ImportarProdutos::dispatch($dados['limite'] ?? 30, $dados['pular'] ?? 0);

        return response()->json([
            'message' => 'Importação enfileirada. Rode "php artisan queue:work" para processar.',
        ], 202);
    }
}
