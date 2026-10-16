# Identidade e usabilidade

## Direção visual

Anime Tracker usa uma base escura de grafite, superfícies discretas e violeta para as ações principais. Capas e imagens pessoais fornecem a cor e a identidade da coleção. O perfil foi inspirado nas referências locais `imagens perfil/1.png`, `2.png` e `3.png`: banner amplo, avatar sobreposto, estatísticas ao lado e coleções com capas em destaque. As referências não foram copiadas como assets da aplicação.

Os quatro indicadores do perfil são favoritos, assistindo, concluídos e em espera. Seus valores são totais reais da coleção, independentes da página ou do filtro. Valores privados não são expostos para visitantes; a espera continua exclusiva do proprietário.

## CSS

`resources/css/app.css` importa nesta ordem:

1. `foundation.css`: Tailwind, tokens, tipografia, botões, campos e utilitários compartilhados.
2. `chrome.css`: navegação, filtros, abas e paginação.
3. `cards.css`: capas, cards, ações, status e progresso.
4. `pages.css`: perfil, dashboard, detalhes, autenticação e formulários.
5. `responsive.css`: adaptação por largura e preferência de movimento reduzido.

Tokens centrais: canvas `#0c0f16`, superfície `#141923`, texto `#edf0f7`, texto secundário `#939dae`, acento `#a894ff` e ação `#7c5ce7`. A fonte usa Segoe UI/system-ui, sem depender de serviço externo. Tailwind usa modo escuro por classe, aplicado pelos layouts.

Use `btn`, `btn-primary`, `btn-secondary`, `btn-quiet`, `btn-danger` e `btn-sm` para ações; `form-field` para campos; `page-wrap`, `page-heading` e `section-heading` para estrutura. Evite voltar a introduzir paletas diferentes em cada tela.

## Cards navegáveis

O título é um link HTML real com classe `card-link`. Seu pseudo-elemento cobre o card inteiro. A área `card-actions` fica em uma camada superior, preservando botões, formulários e links independentes. Não envolver o card inteiro em outro link e não acrescentar um clique JavaScript ao article. Isso preserva Tab/Enter, abrir em nova aba e o comportamento dos controles.

Cards locais abrem `anime.show`: capa, sinopse, gêneros e avaliações. Resultados Jikan ainda não importados indicam explicitamente que abrem o MyAnimeList em outra aba; títulos já existentes abrem os detalhes locais.

Favoritos usam o mesmo poster-wrap dos demais cards: proporção 3/3.8, object-fit cover e hover compartilhado, sem exceção para imagens de demonstração. Cada bloco tem sua ordem independente: `top_position`, `favorite_position` e `completed_position`. As setas recebem o contexto da coleção e nunca alteram outra ordem. Ações dessas coleções no perfil usam Fetch com os formulários Blade existentes e sincronizam fragmentos alterados, indicadores e navegação, sem recarregar a página; sem JavaScript, os formulários mantêm o comportamento convencional. O componente Pretendo assistir resolve o status da conta autenticada, inclusive ao visitar outra coleção.

Componentes principais: `anime-poster`, `plan-card`, `profile-anime-card`, `status-badge`, `profile-stat`, `timeline-entry`, `list-filters`, `page-heading`, `empty-state` e `icon`. Ícones são SVGs estáticos internos, sem biblioteca ou serviço externo.

`collection-order-button` concentra as setas e seus rótulos. O perfil usa parciais em `profile/collections`, compartilhados pela página e pela resposta JSON de fragmentos. O módulo `resources/js/collections/profile.js` envia a mutação, lê somente os blocos indicados e preserva rolagem/foco. O Top 10 mantém um elemento raiz oculto quando vazio, permitindo recriar o bloco sem navegação. Expiração de sessão e falha de atualização após salvar têm mensagens distintas. Dados externos de busca chegam aos templates como `CatalogAnime`, sem acesso ao JSON específico da Tenrai.

## Filtros e navegação

- Catálogo: título, gênero, status pessoal, fora da lista, favoritos, situação de exibição, duração e ordenação por inclusão/título/média pública/episódios.
- Biblioteca e concluídos: título, gênero, favoritos e ordenação; a biblioteca tem abas por status.
- Espera: título, gênero, favoritos e ordenação.
- Perfil: título e gênero nas coleções, preservando os indicadores globais.
- Pessoas: nome ou username, com ou sem `@`, e ordenação por nome ou inclusão.

Filtros são GET, validados por `ListingFiltersRequest` e aplicados por query objects. A paginação mantém os parâmetros; no perfil e na biblioteca, volta à seção correspondente. Um novo envio do formulário reinicia a paginação.

## Acessibilidade e validação

Há link para pular navegação, rótulos nos filtros, controles com nomes acessíveis, foco visível, menu responsivo com Escape/aria-expanded, textos de estado e respeito a movimento reduzido. O menu móvel tem rolagem própria para caber em telas baixas.

Testes HTTP/DOM e build validam estrutura, rotas, filtros e regras. Após a indisponibilidade inicial do navegador integrado, a revisão foi executada no Edge: entrada, painel, perfil, catálogo, detalhes e biblioteca em desktop; biblioteca, menu e perfil em viewport móvel de 390 × 844. Busca por título, abertura dos detalhes pelo card e abertura do planejamento foram exercitadas pela interface. Banner e avatar visíveis carregaram; o perfil móvel não apresentou transbordamento horizontal da página. A navegação de seções do perfil possui rolagem horizontal própria. Esta revisão amostral não substitui uma auditoria completa de acessibilidade ou de todos os dispositivos.
