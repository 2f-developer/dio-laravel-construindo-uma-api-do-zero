<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class CepController extends Controller
{
    /** Consulta um endereço no ViaCEP. O CEP vem na rota, sempre com 8 dígitos. */
    public function show(string $cep): JsonResponse
    {
        try {
            $resposta = Http::timeout(10)->acceptJson()->get("https://viacep.com.br/ws/{$cep}/json/");
        } catch (ConnectionException) {
            return response()->json(['message' => 'ViaCEP indisponível no momento.'], 503);
        }

        if ($resposta->failed()) {
            return response()->json(['message' => 'ViaCEP respondeu com erro.'], 502);
        }
        if ($resposta->json('erro')) {
            return response()->json(['message' => 'CEP não encontrado.'], 404);
        }

        return response()->json(['data' => Arr::only($resposta->json(), ['cep', 'logradouro', 'bairro', 'localidade', 'uf'])]);
    }
}
