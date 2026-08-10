<?php

// Lê direto do composer.json (já é a fonte de verdade validada pelo CI contra a
// tag de deploy - ver "Validar tag x versão declarada" em cd-homolog.yml/cd-prod.yml)
// em vez de manter um valor hardcoded que precisa ser lembrado a cada release.
return [
    'current' => env('APP_VERSION', json_decode(file_get_contents(base_path('composer.json')), true)['version'] ?? 'desconhecida'),
];
