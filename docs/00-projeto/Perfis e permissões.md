---
title: Perfis e permissões
tags:
  - projeto
  - seguranca
---

# Perfis e permissões

## Os 8 perfis

O perfil é uma **coluna `enum` única** em `users.role`. Não existe tabela de papéis, nem de
permissões, nem pacote de ACL.

| Perfil | Papel no fluxo |
|---|---|
| `protocolo` | Entrada do pacote de faturas no sistema |
| `lisura` | Análise de conformidade e abertura de glosas |
| `sire` | Autorização de pagamento |
| `glosa` | Gestão de recursos e contestações sobre valores glosados |
| `arquivo` | Arquivamento do pacote já processado |
| `pagamento` | Mapas de pagamento (acrescentado depois dos demais) |
| `auditor` | Só visualização |
| `admin` | Acesso total |

Os seis primeiros são **etapas do fluxo**, não níveis hierárquicos. Ver
[[Ciclo de vida do pacote]].

## Como a autorização realmente funciona

> [!danger] O README está errado sobre isto
> O README afirma "8 perfis com permissões granulares". A contagem está certa; **"granulares"
> não**. Existem apenas 5 gates grossos, e a maior parte da autorização real está espalhada
> dentro dos métodos dos controllers, onde não é auditável.

Há três mecanismos convivendo, em ordem decrescente de confiabilidade:

### 1. Gates (`app/Providers/AuthServiceProvider.php`)

| Gate | Quem passa | Rotas protegidas |
|---|---|---|
| `admin` | `admin` | 30 |
| `mapas-view` | `admin`, `pagamento`, `auditor` | 9 |
| `mapas-manage` | `admin`, `pagamento` | 9 |
| `anular-pacotes` | `admin` | 3 (deveria ser 4 — ver abaixo) |
| `admin-or-pagamento` | `admin`, `pagamento` | **0 — definido e nunca usado** |

### 2. Checagens *ad hoc* dentro dos métodos

O grosso da autorização. São ~140 chamadas a `hasRole()` espalhadas por controllers e views,
no formato:

```php
if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('protocolo')) {
    // nega
}
```

Distribuição aproximada: `protocolo` 25, `glosa` 19, `arquivo` 16, `sire` 14, `lisura` 14.

O problema não é o estilo — é que **a tabela de rotas deixa de dizer a verdade sobre quem pode
o quê**. Não dá para auditar a segurança do sistema sem ler os 5.900 linhas de controller.

### 3. Middleware `CheckRole`

`app/Http/Middleware/CheckRole.php` existe e **nunca foi registrado nem usado**. O
`withMiddleware` de `bootstrap/app.php` está vazio. É código morto que aparenta ser proteção
ativa.

## Falhas conhecidas

> [!bug] `anulacao.ver` está desprotegida
> A rota `/configuracoes/anulacao/ver/{id}` (`routes/web.php:222`) foi declarada **fora** do
> grupo dos irmãos dela. `buscar`, `anular` e `listar` exigem `can:anular-pacotes`; `ver`
> exige só `auth`.
>
> Efeito: **qualquer usuário autenticado**, incluindo `auditor` e `protocolo`, consegue ler o
> detalhe financeiro de pacotes anulados. Rastreado em [[Pendências]].

> [!bug] Mutações financeiras sem checagem de papel na rota
> As 16 rotas de `pacotes/*` — incluindo transições destrutivas como `arquivar`, `pagamento` e
> `analise-recurso` — são `auth`-only. As 6 de `relatorios/*` e as 13 de `graficos/*` também,
> e essas expõem dados financeiros e **notas de desempenho por colaborador**.

> [!bug] PII em log a cada requisição
> O gate `admin` escreve um `Log::info` com e-mail e papel do usuário **a cada checagem** —
> ou seja, a cada carregamento de página administrativa. Ruído de log e dado pessoal
> gravado sem necessidade.

A correção das três está planejada em F4.1 do plano de normalização, e depende da rede de
testes (F3) existir antes.
