# API de produtos com Laravel

API REST de catálogo de produtos feita para o lab "Laravel: Construindo uma API do Zero", da formação PHP Experience da DIO. O lab trata de três coisas: expor dados por rotas HTTP, consumir outras APIs por HTTP e configurar filas. O repositório da aula é o [danbonattis/dio-laravel-api](https://github.com/danbonattis/dio-laravel-api). Aqui o projeto foi refeito no Laravel atual, com banco de dados de verdade em vez de dados em memória.

## O que a API faz

**Produtos e categorias (CRUD).** Listagem com filtros e paginação, cadastro e edição com validação (Form Requests), ajuste de estoque e estatísticas do catálogo. As respostas usam API Resources, então o formato do JSON não depende da estrutura das tabelas.

**Consumo de APIs externas.** `GET /api/cep/{cep}` consulta o ViaCEP e devolve só os campos úteis. CEP fora do formato nem sai do servidor (404 pela própria rota), CEP inexistente vira 404 e o ViaCEP fora do ar vira 503, em vez de um erro 500 genérico.

**Fila.** `POST /api/importacoes` coloca na fila o job `ImportarProdutos`, que busca produtos no catálogo público da [DummyJSON](https://dummyjson.com/docs/products) e cria ou atualiza produtos e categorias no banco. A requisição responde na hora com `202 Accepted`, e quem faz o trabalho é o worker. Se a API externa falhar, o job tenta de novo até 3 vezes, esperando 10, 30 e 60 segundos. Como o SKU identifica o produto, importar de novo atualiza em vez de duplicar.

## Rotas

| Método | Rota | O que faz |
| --- | --- | --- |
| `GET` | `/api/health` | status da API |
| `GET` `POST` | `/api/products` | lista (com filtros) / cria |
| `GET` `PUT` `PATCH` `DELETE` | `/api/products/{id}` | mostra / edita / remove |
| `PATCH` | `/api/products/{id}/stock` | ajusta o estoque |
| `GET` | `/api/products/statistics` | totais do catálogo |
| `GET` `POST` | `/api/categories` | lista / cria |
| `GET` `PUT` `PATCH` `DELETE` | `/api/categories/{id}` | mostra / edita / remove |
| `GET` | `/api/categories/statistics` | produtos por categoria |
| `GET` | `/api/cep/{cep}` | endereço pelo ViaCEP |
| `POST` | `/api/importacoes` | enfileira importação (`limite` 1–100, `pular`) |

## Rodando

Precisa de PHP 8.3+ e Composer. O banco padrão é SQLite e a fila usa o driver `database`, então não precisa de nenhum serviço extra.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Para a fila funcionar, deixe um worker rodando em outro terminal:

```bash
php artisan queue:work
curl -X POST localhost:8000/api/importacoes -H 'Content-Type: application/json' -d '{"limite": 30}'
```

## Testes

```bash
php artisan test
```

São 26 testes de feature com banco em memória. Eles cobrem o CRUD e a validação de produtos e categorias, o enfileiramento da importação (com `Queue::fake`), o job contra um catálogo simulado (com `Http::fake`): criação, atualização sem duplicar, item inválido ignorado e falha da API externa, e a consulta de CEP nos casos de sucesso, inexistente, formato errado e ViaCEP fora do ar. Nenhum teste depende de internet. O código segue o estilo do Laravel Pint, e o GitHub Actions roda Pint e testes a cada push.
