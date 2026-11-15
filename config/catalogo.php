<?php

return [
    // Catálogo externo usado pela importação. A DummyJSON tem uma API pública de produtos.
    'url' => env('CATALOGO_URL', 'https://dummyjson.com/products'),
    'timeout' => (int) env('CATALOGO_TIMEOUT', 15),
];
