---
title: Anulação e auditoria
tags:
  - dominio
  - seguranca
---

# Anulação e auditoria

Anular um pacote é invalidá-lo depois de registrado. É a operação mais sensível do sistema:
mexe em valores financeiros já lançados, e por isso tem trilha de auditoria dedicada.

## Quem pode

Só `admin`. O gate é `anular-pacotes` em `AuthServiceProvider`.

## Quando pode

`Pacote::podeSerAnulado()` (`app/Models/Pacote.php:196`):

- o pacote não pode já estar anulado;
- **não pode estar `arquivado`** — depois de arquivado, é definitivo.

Note que `arquivo` (a etapa) e `arquivado` (o estado final) são coisas diferentes: um pacote
em `arquivo` ainda pode ser anulado; um `arquivado`, não.

## A trilha

A anulação grava em `pacotes_anulados_audit` (model `PacoteAnuladoAudit`) um **snapshot
desnormalizado** de ~24 colunas `*_original` — `valor_fatura_original`,
`valor_glosa_original`, `data_entrada_original` e assim por diante.

Isso é deliberado: o registro de auditoria precisa sobreviver a qualquer alteração posterior
no pacote. Se ele apenas apontasse para o `pacote_id`, uma edição futura reescreveria a
história.

Acessores úteis: `valorTotalOriginal`, `impactoFinanceiro`. Escopos: `porPeriodo`, `porUsuario`.

## Problemas conhecidos

> [!bug] Três campos para o mesmo conceito
> `podeSerAnulado()` e `isAnulado()` consultam **três fontes diferentes** para saber se um
> pacote está anulado:
>
> ```php
> $this->anulado === true
>     || $this->localizacao_atual === 'anulado'
>     || $this->estado_geral === 'Anulado'
> ```
>
> São três colunas que podem divergir entre si. O `isAnulado()` usa `||` (basta uma) e o
> `podeSerAnulado()` usa `&&` (todas precisam estar limpas) — o que é defensivo, mas trata o
> sintoma. A causa é o campo ter sido acrescentado três vezes, em três migrations distintas
> com o mesmo nome. Ver [[Modelo de dados]] e [[Pendências]].

> [!bug] `anular()` não usa transação
> `Pacote::anular()` (linha 217) altera o pacote e grava a auditoria sem envolver as duas
> operações numa transação de banco, e lança `\Exception` genérica. Uma falha entre as duas
> escritas deixa o sistema num estado inconsistente — pacote anulado sem registro de auditoria,
> ou o contrário.

> [!bug] A tela de consulta de anulação está desprotegida
> `anulacao.ver` (`routes/web.php:222`) exige apenas `auth`, enquanto `buscar`, `anular` e
> `listar` exigem `can:anular-pacotes`. Qualquer usuário autenticado lê o detalhe financeiro
> de pacotes anulados. Ver [[Perfis e permissões]].

> [!warning] O registro de movimentação pode silenciosamente não acontecer
> `registrarMovimentacaoAnulacao()` está protegido por
> `class_exists('\App\Helpers\AtividadesHelper')`. Se a classe não existir, o método **não faz
> nada e não avisa**. Guard defensivo que transforma um erro em perda silenciosa de auditoria.
