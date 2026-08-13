---
title: Mapa de rotas e autorização
tags:
  - arquitetura
  - seguranca
---

# Mapa de rotas e autorização

`routes/web.php` — 153 linhas, **105 rotas explícitas**, mais `Auth::routes()` (~11) e um
`fallback`. Tudo dentro de um grupo `auth` externo.

> [!info] Não existe API
> Não há `routes/api.php`. O sistema é inteiramente server-rendered com Blade; os endpoints
> de gráfico retornam JSON, mas por rotas web. `laravel/sanctum` está instalado e não é usado.

## Superfície por grupo

| Grupo | Proteção | Rotas |
|---|---|---|
| `pacotes/*` | `auth` | 16 |
| perfil | `auth` | 3 |
| bloco administrativo | `can:admin` | 30 |
| `relatorios/*` | `auth` | 6 |
| pesquisa | `auth` | 8 |
| `graficos/*` | `auth` | 13 |
| mapas (visualização) | `can:mapas-view` | 9 |
| mapas (gestão) | `can:mapas-manage` | 9 |
| anulação | `can:anular-pacotes` | 3 (+1 desprotegida) |

Públicas e intencionais: `/`, `/health`, `/up`, `Auth::routes()`, `fallback`.

## Onde a autorização falha

Detalhado em [[Perfis e permissões]]. Em resumo: **35 rotas são `auth`-only cobrindo mutação
financeira e relatórios**, e a checagem real de papel está dentro dos métodos, o que torna a
tabela de rotas insuficiente para auditar quem pode o quê.

A rota `anulacao.ver` (linha 222) ficou fora do grupo protegido dos irmãos dela.

## Armadilhas de roteamento

> [!bug] Segmento duplicado em duas rotas
> Dentro do prefixo `pacotes`, duas rotas repetem o segmento:
>
> ```
> /pacotes/pacotes/{id}/retirada-oficio-glosa   (linha 62)
> /pacotes/pacotes/{id}/prazos                  (linha 64)
> ```
>
> Funcionam — a URL real é essa mesma —, mas denunciam edição por cópia e colam mal com as
> irmãs. Corrigir exige atualizar as views que geram os links.

> [!warning] `/pacotes/{id}` é declarado cedo
> Está na linha 46, antes de outras rotas do mesmo prefixo. Rotas literais declaradas depois
> de um parâmetro curinga correm risco de nunca serem alcançadas. Ao acrescentar rota nova em
> `pacotes/*`, **declare-a antes** de `/{id}` ou confirme com `php artisan route:list`.

> [!bug] `routes/web.txt` é uma cópia obsoleta e versionada
> 231 linhas, divergente do `web.php` real, no repositório. É perigosa justamente por parecer
> fonte da verdade. Remoção planejada em F4.0.

## Ferramenta de verificação

A tabela de rotas real, sempre:

```bash
docker compose -f deploy/compose.prod.yml exec -T app php artisan route:list
```
