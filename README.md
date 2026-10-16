# Anime Calendar / Anime Tracker

Aplicação Laravel para organizar animes, agendas semanais, progresso, avaliações, favoritos e Top 10. A comunidade tem uma agenda global administrada; cada conta mantém sua biblioteca pessoal.

## Executar localmente

Requisitos: PHP 8.2+, Composer, MySQL e Node compatível com Vite 7 (20.19+ ou 22.12+). Habilite PDO MySQL, PDO SQLite para testes, mbstring, fileinfo e as extensões exigidas pelo Composer.

Em uma instalação nova:

~~~powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
# Ajuste a conexão MySQL no .env antes de migrar.
php artisan migrate
php artisan storage:link
npm run build
php artisan serve
~~~

Acesse http://127.0.0.1:8000. O document root de uma hospedagem deve apontar para a pasta public. Fotos são servidas em /storage, sem /public na URL. Configure APP_URL para o endereço real e APP_TIMEZONE para o fuso desejado (padrão America/Sao_Paulo).

Para desenvolvimento com atualização automática, use composer dev; também é possível executar php artisan serve e npm run dev em terminais separados. O comando Pail é opcional e não integra o script multiplataforma.

Em uma instalação existente, preserve .env e APP_KEY. Use migrations incrementais; não use migrate:fresh para atualizar este projeto com dados.

## Dados de demonstração

~~~powershell
php artisan db:seed
~~~

DemoSeeder roda apenas em local/testing. Cria catálogo fictício com imagens locais, perfis públicos/privados, planos, logs, avaliações e rankings. Também promove o primeiro usuário existente a admin sem alterar sua senha. A reexecução completa registros ausentes e redefine perfis demo; não é uma limpeza do banco.

Conta administrativa sintética: admin@anime-calendar.test. Contas comuns: usuario01@anime-calendar.test até usuario19@anime-calendar.test. A senha de demonstração está definida em database/seeders/DemoSeeder.php. Não utilizar essas contas em produção.

## Arquitetura

Monólito modular com Eloquent e injeção de dependências do Laravel:

- routes/catalog.php, profiles.php, planning.php e admin.php: entradas de cada área; web.php compõe os módulos.
- app/Http/Requests: validação de entrada e autorização de importação/avaliação.
- app/Policies/WatchPlanPolicy.php: acesso a planos e cópia de agendas visíveis.
- app/Services/Planning: motor de projeção sem escrita e operações transacionais de agenda, logs, pausa, conclusão e cópia.
- app/Services/Library: atualização serializada do ranking; LibraryController coordena favoritos e avaliações.
- app/Services/Profiles: persistência de uploads e limpeza após sucesso da alteração.
- app/Queries: consultas reutilizáveis, paginação e agregações no banco.
- resources/views/planning e components: apresentação compartilhada entre contexto global e pessoal.

Controllers traduzem HTTP em chamadas ao domínio. Regras compartilhadas não devem ser copiadas para controllers ou Blade. Repositories que apenas espelham Eloquent não são necessários. Datas de plano são dias de calendário; logs reais substituem previsão; dias variados sem log não avançam. Pausas são intervalos de dias com fim exclusivo. Leituras nunca concluem planos no banco: conclusão é uma ação explícita.

Planos globais têm user_id nulo. Planos pessoais têm proprietário. A coluna gerada owner_key permite unicidade também para os globais. Deletar uma conta apaga seus planos pessoais por cascata. A migração recusa dados duplicados; nenhuma deduplicação destrutiva é automática.

Perfis públicos são visíveis dentro da comunidade autenticada. Dados privados não podem ser copiados por terceiros. Reviews comunitárias respeitam is_public e show_reviews_public. E-mail permanece opcionalmente verificável; não há bloqueio obrigatório por verificação nesta versão.

Calendário exibe o próximo mês; histórico permite consultar até 366 dias por vez, com período inicial de 90 dias. Progresso é calculado por semanas e exceções (logs/pausas), sem percorrer todos os dias desde o início em cada card.

O status **Pretendo assistir** guarda animes para depois, sem iniciar progresso, agenda ou histórico. Está disponível no catálogo, busca externa e detalhes, com lista em **Meu espaço → Pretendo assistir** e no próprio perfil (apenas para o dono). “Começar a assistir” abre o formulário para escolher a data e a agenda; o status só muda depois de salvar. Salvar de novo não duplica nem substitui o status de um anime que já está na biblioteca.

## Verificação

~~~powershell
composer check
npm run build
npx playwright install chromium
npm run test:browser
php scripts/benchmark-collections.php
~~~

composer check executa Laravel Pint e Pest/PHPUnit. phpunit.xml usa SQLite em memória, cache e sessão em array e e-mail em array; não aponta para o MySQL de desenvolvimento. Os testes de busca externa usam HTTP fake, sem rede. CI está definida em .github/workflows/checks.yml.

Os testes de navegador usam Playwright/Chromium e um servidor exclusivo na porta 8011 (ou `E2E_PORT`), com SQLite temporário em `storage/framework/testing`, chave de aplicação própria e fixtures dedicadas. Eles não reutilizam o servidor ou o MySQL de desenvolvimento. Relatórios e traces ficam em `playwright-report`/`test-results`, ignorados pelo Git. A CI também executa esses testes e publica artefatos em caso de falha.

O benchmark cria um banco SQLite em memória no próprio processo e mede movimentos em coleções de 100, 1.000 e 10.000 itens. Ver `PERFORMANCE.md` para resultados e limites da medição; não é teste de carga de produção.

### Busca externa de animes

A busca usa a Tenrai v1 (`https://api.tenrai.org/v1`). `AnimeCatalogProvider` define o contrato e `TenraiProvider` converte os campos externos para `CatalogAnime`, formato interno consumido pelo controller e pelos templates. Uma nova integração implementa esse contrato e troca o binding em `AppServiceProvider`, sem modificar as telas. Ela deve fornecer o ID MAL para preservar a identidade dos animes já importados.

`AnimeSearchService` mantém cache de 10 minutos por identidade do provedor/título e mensagem amigável quando a origem falha. O adaptador Tenrai configura limites de conexão/resposta. Falhas são registradas no log sem incluir o título pesquisado. O endereço pode ser configurado com `ANIME_CATALOG_BASE_URL` (veja `.env.example`); após trocar uma configuração em cache, execute `php artisan config:clear`. Alterar apenas a URL exige um provedor compatível com Tenrai/Jikan v4. A API pública antiga da Jikan foi encerrada em outubro de 2026.

Consulte PLANO_DE_REFORMULACAO.md para a estratégia e DIARIO_DO_PROJETO.md para o estado de execução, histórico, resultados e limitações. AGENTS.md orienta sessões futuras a manter esses registros.

## Interface e filtros

As telas compartilham uma identidade escura com capas em destaque, acentos violeta e componentes responsivos. O perfil tem banner, avatar e quatro indicadores da coleção: favoritos, assistindo, concluídos e em espera, respeitando a privacidade.

Os cards locais abrem os detalhes do anime ao clicar na capa ou na área do card. Botões como iniciar, favoritar e remover continuam independentes. O catálogo oferece filtros por título, gênero, status pessoal, favoritos, exibição e duração, além de ordenação. Biblioteca, concluídos, espera e perfil também têm filtros próprios; a paginação preserva as escolhas.

Consulte DESIGN_SYSTEM.md para os tokens, componentes, comportamento dos cards e convenções visuais.

As ações das coleções do perfil enviam POST/PATCH com CSRF e recebem JSON com a mensagem e os blocos afetados. Uma leitura autenticada do perfil com `Accept: application/json` e `blocks[]` retorna somente os parciais solicitados, com os mesmos filtros, paginação e regras de privacidade. O HTML completo usa esses mesmos parciais. Sem JavaScript, os formulários continuam redirecionando normalmente.
