<?php

return [

    /*
    |--------------------------------------------------------------------
    | Prazo de recurso de glosa (em dias)
    |--------------------------------------------------------------------
    |
    | Dias corridos, contados a partir de `data_retirada_oficio`, que a
    | OCS/PSA tem para apresentar recurso antes de o pacote ser
    | considerado "recurso não recebido" (docs/40-decisoes/ADR-12.md).
    |
    | O valor não dispara nenhuma ação automática — apenas alimenta o
    | relatório de aviso (specs/003-relatorio-prazo-glosa). Ver
    | docs/10-dominio/Glosa, recurso e prazos.md.
    |
    */
    'prazo_recurso_dias' => env('PMED2_PRAZO_RECURSO_DIAS', 30),

];
