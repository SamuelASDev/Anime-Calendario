# Diário do Anime Calendar / Anime Tracker

Última atualização: 2026-10-15.

Branch de trabalho atual: `dev`. Ela recebeu todos os commits locais da `main` até `109d3f7`; a reformulação e as alterações desta sessão estão reunidas no commit desta entrega. O stash de segurança foi mantido.

## Ponto de retomada

**Concluído:** reformulação modular documentada em PLANO_DE_REFORMULACAO.md e implementada. Controllers delegam às camadas de Requests/Policies/Queries/Services; agenda/progresso têm motor compartilhado; banco local migrado sem limpeza; telas de planejamento e perfil compartilham componentes.

**Validação atual:** 66 testes aprovados, 273 assertions, após ajustar o início de Pretendo assistir para abrir o formulário do plano. Build e validações finais registrados abaixo. MySQL local preservado: 21 usuários, 16 animes, 217 planos. O plano adicional e um metadado adicional já existiam no backup anterior à reformulação.

**Última entrega:** status Pretendo assistir disponível no catálogo, busca, detalhes, biblioteca e perfil do proprietário. Lista própria em /me/watch-later; espera não avança episódios.

**Próximos trabalhos opcionais:** teste visual em navegador e acessibilidade, benchmark de volume, localização completa PT-BR, verificação obrigatória de e-mail se desejada e integração de CI executada no remoto. Nenhuma dessas tarefas está em andamento. A revisão P01–P24 abaixo descreve a baseline antiga; consulte a tabela de resolução no final antes de tratá-los como problemas atuais.

## Como usar este diário

1. Leia o ponto de retomada, os problemas e a última entrada do histórico.
2. Confira `git status --short`, código e ambiente: este documento é um retrato, não uma consulta em tempo real.
3. Ao trabalhar, atualize status e evidências dos itens afetados.
4. Ao encerrar, acrescente uma entrada ao histórico com antes/depois, arquivos, validação, pendências e próximo passo.

Status usados: **concluído**, **pendente**, **em andamento**, **bloqueado**. Diferencie constatação por leitura de reprodução automatizada e hipótese que ainda exige teste.

## Ambiente e estado inicial observado

- Workspace: `C:\Users\samue\Desktop\Programação\Animezud\anime-calendar`.
- Windows / PowerShell; PHP CLI 8.2.12 em `C:\xampp\php\php.exe`; Composer em `C:\composer\composer.bat`.
- Backend Laravel 12, autenticação baseada no Breeze, Eloquent e MySQL `anime_calendar` em ambiente `local`.
- Frontend Blade, Tailwind CSS 3, Alpine.js, Axios e Vite. O build instalado identificou Vite 7.3.1.
- `APP_URL` local observado: `http://localhost`; o usuário acessa `http://127.0.0.1:8000`. URLs geradas fora de requisições merecem atenção.
- `config/app.php` define timezone `UTC`; o usuário trabalha em `America/Sao_Paulo`. Não alterado nesta revisão.
- `public/storage` é uma junction para `storage/app/public`.
- Sessão, cache e fila têm suporte/configuração para banco; não há jobs próprios de domínio identificados.
- Último commit observado: `109d3f7` (`invertendo ordem`). Nenhum commit foi criado nesta sessão.
- Modificações anteriores à documentação: `app/Models/User.php`, `database/seeders/DatabaseSeeder.php` e novo `database/seeders/DemoSeeder.php`. Preserve essas alterações.

## Mapa do código na revisão inicial (anterior à reformulação)

| Área | Arquivos principais | Responsabilidade |
|---|---|---|
| Inicialização | `bootstrap/app.php`, `bootstrap/providers.php`, `public/index.php`, `artisan` | Rotas web/console, health check e alias `admin` |
| Rotas | `routes/web.php`, `routes/auth.php`, `routes/console.php` | 72 rotas próprias listadas; autenticação, catálogo, calendários, perfil e administração |
| Catálogo | `app/Http/Controllers/AnimeController.php`, `app/Services/JikanService.php` | Busca Jikan, importação, detalhes, sinopse e paginação |
| Global/live | `app/Http/Controllers/WatchPlanController.php` | Planejamento global, calendário, histórico, reviews e atalho para concluídos pessoais |
| Pessoal | `app/Http/Controllers/PersonalWatchPlanController.php` | CRUD do usuário, logs, pausa, conclusão, favoritos, Top 10 e cópia de planos |
| Administração | `app/Http/Controllers/Admin/WatchPlanAdminController.php`, `app/Http/Middleware/AdminMiddleware.php` | Gestão dos planos globais e validação de `role === admin` |
| Perfil | `app/Http/Controllers/ProfileController.php`, `app/Http/Requests/ProfileUpdateRequest.php` | Conta, uploads, privacidade e perfil por username |
| Autenticação | `app/Http/Controllers/Auth/`, `app/Http/Requests/Auth/LoginRequest.php` | Registro/login/logout, recuperação, senha e verificação; login limita tentativas |
| Dados | `app/Models/`, `database/migrations/` | Seis models de domínio; 17 migrations incluindo infraestrutura |
| Demonstração | `database/seeders/DemoSeeder.php`, `DatabaseSeeder.php` | Fixtures locais repetíveis e promoção do primeiro usuário |
| Interface | `resources/views/` | Layouts, componentes Breeze e telas de catálogo, perfil, global, pessoal e admin |
| Assets | `resources/js/`, `resources/css/`, configs Vite/Tailwind/PostCSS | Alpine e Axios; CSS utilitário; logo em `public/images/logoSapo.png` |
| Testes | `tests/Feature/`, `tests/Unit/`, `tests/Pest.php`, `phpunit.xml` | Pest/PHPUnit; cobertura concentrada em autenticação/conta |
| Configuração | `config/`, `.env.example`, `composer.json`, `package.json` | Banco, storage, mail, cache, sessão, fila e scripts |

### Modelo de dados e regras atuais

- `users`: dados de conta, role, username único nullable, bio, fotos/banner e flags de privacidade.
- `animes`: `mal_id` único, título, episódios nullable, sinopses original/PT, gêneros em texto separado por vírgulas, status e URL de imagem.
- `watch_plans`: anime, proprietário nullable, episódio base, ritmo, data inicial e status (`assistindo`, `pausado`, `concluido`).
- `watch_plan_days`: configuração semanal, dia 0=domingo a 6=sábado, quantidade planejada e flag de dia variável.
- `watch_logs`: episódios reais por plano/data e notas. Log substitui a previsão daquele dia.
- `user_anime_meta`: nota de 1 a 10, comentário, favorito e posição no Top 10 por usuário/anime.
- Progresso atual: episódio base mais logs ou previsão dos dias anteriores a hoje, limitado ao total conhecido. Dias variáveis sem log não avançam.
- A projeção de progresso aparece no model e em controllers; calendário/histórico também percorrem datas. Há duplicação substancial.
- Cadastro gera username a partir do nome; factory padrão não gera username.
- Perfis chamados públicos ainda exigem login por estarem no grupo `auth`.

## Revisão inicial: problemas e próximos trabalhos

Registro histórico da revisão inicial: os itens abaixo foram identificados por leitura do código, salvo indicação de reprodução. O texto original foi preservado; o estado atualizado aparece na seção de resolução da reformulação ao final.

### Prioridade alta: autorização e separação dos dados

- **P01 — Pendente — Manutenção acessível sem login.** `routes/web.php` expõe `GET /arruma-tudo-hosting` fora de middleware de autenticação. Executa `storage:link` e limpeza de caches e devolve saída/erros em HTML. Remover a rota e usar CLI, ou desenhar manutenção autenticada apropriada. Critério: visitante não consegue executar manutenção por HTTP. Não invoquei essa rota durante a revisão.
- **P02 — Pendente — Histórico global altera qualquer plano.** `WatchPlanController::confirm` valida apenas existência de `plan_id`; não verifica admin nem `user_id`. `POST /history/confirm` exige apenas login. Um usuário pode enviar o ID de um plano pessoal alheio e alterar logs. Critério: usuário comum não altera histórico global nem plano de terceiros; usar testes com duas contas e plano global.
- **P03 — Pendente — Criação global pode sobrescrever plano pessoal.** Rotas `watch-plans.create/store` estão apenas em `auth`; `WatchPlanController::store` faz `updateOrCreate` por `anime_id`, sem `user_id = null`. Com os seeds, existem vários planos por anime. Critério: somente papel autorizado grava plano global e a consulta identifica explicitamente o escopo global, preservando todos os planos pessoais.
- **P04 — Pendente — Sinopse global editável por qualquer conta.** Rotas de editar/atualizar sinopse estão no grupo `auth`; `AnimeController::updateSynopsis` não verifica papel. Confirmar política do produto e aplicar autorização coerente. Critério: usuário sem permissão recebe 403 e não altera o catálogo.

### Prioridade média: fluxos, integridade e privacidade

- **P05 — Pendente — Teste de perfil quebrado, reproduzido.** `UserFactory` não define `username`; `layouts/navigation.blade.php` monta `route('profile.show', auth()->user()->username)` sem fallback. `ProfileTest > profile page is displayed` retorna 500 por parâmetro ausente. Dashboard e diretório de usuários também assumem username. Corrigir factory e definir tratamento para contas legadas sem username; não apenas ocultar a falha do teste. Critério: suíte verde e conta legada consegue abrir suas telas.
- **P06 — Pendente — Adição pessoal bloqueada para usuário comum.** `anime.store` está no grupo `auth,admin`, mas formulários de busca e catálogo usam essa rota também com `target=personal`. Critério: conta comum pode importar anime para seu espaço, mantendo escrita global protegida.
- **P07 — Pendente — Escopo de cópia e admin sem restrição explícita.** `followFromGlobal` aceita qualquer `WatchPlan` por ID e não valida visibilidade do proprietário. A view de perfil também usa essa ação para copiar plano pessoal público, portanto decidir a regra antes de restringir a globais. Ações admin recebem qualquer plano por binding, embora o índice liste só globais. Critério: cópia respeita privacidade e admin global não modifica planos pessoais por engano.
- **P08 — Pendente — Pausa não congela progresso.** Cálculos percorrem o período inteiro sem considerar `watch_status` nem intervalos de pausa; `pause/resume` apenas alteram string. Índices podem promover um plano pausado a concluído pelo avanço calculado. Definir armazenamento de pausas ou rebasing. Critério: passagem do tempo durante pausa não adiciona episódios; retomar não conta os dias pausados.
- **P09 — Pendente — Conclusão manual e log de hoje.** Cálculos usam datas anteriores a hoje; `complete` adiciona os episódios restantes ao log de hoje. Repetir conclusão hoje pode somar os restantes novamente e telas podem mostrar progresso abaixo do total até amanhã. Critério: concluir duas vezes é idempotente e o progresso exibido é coerente no mesmo dia.
- **P10 — Pendente — GET com escrita e simulação persistente.** Índices pessoal e admin concluem planos no banco durante leitura. Admin aceita `test_date` e usa a projeção simulada para persistir conclusão. Critério: simulação não altera estado real e mudanças de status têm fluxo explícito/testado.
- **P11 — Pendente — Exclusão transforma plano pessoal em global.** Migration de `watch_plans.user_id` usa `nullOnDelete`; apagar conta muda seus planos para `user_id = null`, justamente o marcador de planos globais. Critério: exclusão de conta não publica planos privados; definir cascata ou representação separada de escopo antes de migrar dados.
- **P12 — Pendente — Metadados públicos ignoram privacidade.** `AnimeController::show/allAnimeIndex` carrega reviews de todas as contas; views de catálogo/detalhes/concluídos não filtram `is_public`/`show_reviews_public`. Definir se review é independente do perfil e tornar isso explícito na UX; caso contrário, filtrar no servidor. `profile/show.blade.php` também mostra comentários no Top 10 e recebe `$reviews` sem uma seção de reviews correspondente. Critério: configuração de privacidade tem comportamento documentado e testado em todas as telas.
- **P13 — Pendente — Unicidade só na aplicação.** Faltam índices únicos para usuário/anime em metadados, plano/dia e plano/data em logs. Planos também não impedem duplicidade do mesmo usuário/anime; caso global com NULL precisa de solução compatível com MySQL. `updateOrCreate` sozinho não elimina corrida. Critério: concorrência não duplica dados; migração trata duplicatas existentes.
- **P14 — Pendente — Transações e ranking.** Recriação dos dias do plano e trocas/remoções do Top 10 são múltiplas escritas sem transação/lock. Critério: falha intermediária não deixa plano sem agenda nem ranking duplicado/incompleto.
- **P15 — Pendente — Validação incompleta.** Status de plano aceita qualquer string; `test_date` não tem validação explícita; episódios importados e identificador MAL têm regras permissivas; datas/logs não têm regra de domínio para período válido. Definir enum, limites e cenários de total desconhecido. Cadastro permite nomes de até 255 caracteres, mas edição de username limita 30; padronizar geração e colisões concorrentes.
- **P16 — Pendente — Falhas da API externa sem tratamento.** `JikanService::search` usa `Http::get()->json()` e controller acessa `data['data']` diretamente. Não há timeout configurado pelo projeto, política de retry/cache ou apresentação de erro para resposta inválida/429. Critério: simular indisponibilidade e limitação sem gerar 500 e sem depender de rede nos testes.

### Manutenção, desempenho e ambiente

- **P17 — Pendente — Duplicação de domínio.** Extrair cálculo/projeção para serviço compartilhado depois de criar testes de caracterização. Não alterar implicitamente a regra existente de avanço automático por previsão.
- **P18 — Pendente — Carregamento sem limites.** Índice pessoal carrega todos os planos/logs; admin pagina coleções após carregar tudo; perfis/diretório não paginam; catálogo carrega todas as reviews para calcular média. Perfil acessa `anime->userMeta` sem eager load específico e dashboard consulta relações pelo accessor. Medir queries e migrar agregações/paginação para SQL.
- **P19 — Pendente — Storage e exclusão.** Upload apaga foto anterior antes de garantir nova gravação e atualização no banco; exclusão de conta não remove arquivos. Definir limpeza consistente; confirmar backup e retenção antes de apagar arquivos existentes.
- **P20 — Pendente — Verificação de e-mail não obrigatória.** `User` não implementa `MustVerifyEmail`, apesar de existirem rotas/testes de verificação e middleware `verified` no dashboard. Decidir se produto exige verificação e alinhar model/rotas/testes.
- **P21 — Pendente — Timezone/idioma e interface.** Configuração UTC/en versus público PT-BR afeta virada do dia e calendário. Views misturam textos/classes claras e escuras; revisão visual em navegador não foi realizada. Padronizar depois de confirmar preferência de horário e aparência.
- **P22 — Pendente — Documentação e resíduos.** README mantém URL de clone ilustrativa, imagem `/public/images/logo.png` que não corresponde ao logo existente e exemplos de instalação; canais Twitch diferem entre README/dashboard. Há arquivo vazio `php`, PNG em `app/Http/Controllers/Admin/` e import não usado de `TranslationService` inexistente em `AnimeController`. Avaliar remoção/organização sem apagar assets úteis por suposição.
- **P23 — Pendente — Limitações do seed.** MAL IDs 900000001+ são fictícios, não consultar Jikan usando-os como IDs reais. Seed promove sempre o primeiro usuário e redefine perfis demo na reexecução; não é reset completo: preserva planos/metas existentes e só cria os ausentes. Com fixtures já existentes, `firstOrCreate` não recria capas ausentes; melhorar se necessário. SVGs do seed são gerados internamente; upload normal continua aceitando JPG/PNG/WebP. Fixtures e DB não são versionados; o seeder é a fonte reproduzível.
- **P24 — Pendente — Cobertura de domínio.** Testes existentes cobrem principalmente Breeze, uma home e uma asserção trivial. Faltam cenários de autorização global/pessoal, calendário, pausa/conclusão, privacidade, ranking, upload e seed. Build avisa que Browserslist está desatualizado; pacote `@tailwindcss/vite` v4 está declarado, mas pipeline usa Tailwind 3/PostCSS. Não houve atualização de dependências nem auditoria de vulnerabilidades.

## Verificações executadas em 2026-10-15

| Verificação | Resultado e limites |
|---|---|
| `php artisan migrate --force` | Criou banco/tabela de migrations e aplicou 17 migrations; execução anterior desta sessão |
| `php artisan migrate` | `Nothing to migrate` após criação |
| URLs do model User | Script com bootstrap validou `/storage/...` para foto/banner e null quando ausentes |
| Seed e reexecução | Executados; contagens estáveis, senha da conta original preservada, admin demo validado e imagens demo existentes |
| `php artisan route:list --except-vendor` | 72 rotas próprias listadas |
| PHP lint | 76 arquivos PHP de app/bootstrap/config/database/routes/tests sem erro de sintaxe |
| `php artisan view:cache` | Views compiladas; isso não executa todas as expressões com dados nem valida layout visual |
| `npm run build` | Passou: 54 módulos; aviso de Browserslist antigo. Assets gerados em `public/build`, ignorados no Git |
| `php artisan test --compact` | **24 passaram, 1 falhou; 61 assertions**. Falha P05, parâmetro username ausente no link do perfil |
| Isolamento dos testes | `phpunit.xml`: SQLite `:memory:`; Pest usa `RefreshDatabase` em Feature; não use o MySQL local para esta suíte |
| `git diff --check` | Sem erro antes da criação destes documentos |

Relatório local detalhado dos testes: `storage/logs/code-review-tests.txt` (ignorado pelo Git). Não copiar stack traces completos para o diário. A suíte também foi iniciada durante a inspeção geral; o resultado acima é da execução capturada explicitamente.

Escopo da análise: inventário do código próprio, leitura de backend, models, migrations, seeds, autenticação, configuração e inspeção dos fluxos Blade/JS/CSS e testes. Não auditados linha a linha: `vendor`, `node_modules`, arquivos gerados, lockfiles completos e binários. Não realizados: pentest, auditoria externa de dependências, teste de carga, envio real de e-mails, consulta Jikan real nem validação visual no navegador. Aprovação de build não significa que todos os fluxos funcionam.

## Dados de demonstração

Retrato após seed: 21 usuários, 16 animes fictícios, 216 planos, 1.512 configurações de dias, 306 logs e 210 registros de avaliações/favoritos/ranking. São contagens daquela execução, não valores garantidos após uso da aplicação.

- Primeiro usuário existente, ID 1 (Samuel): promovido a admin; senha original preservada.
- Admin sintético: `admin@anime-calendar.test`; usuários sintéticos: `usuario01@anime-calendar.test` até `usuario19@anime-calendar.test`.
- Credenciais demo estão definidas em `DemoSeeder.php`; não reutilizar como credenciais de produção.
- Perfis demo: `demo_admin` e `demo_user_01` até `demo_user_19`; há perfis privados para testar visibilidade.
- Imagens locais em `storage/app/public/profiles/...` e `storage/app/public/demo/covers/...`.
- Executar `php artisan db:seed` para completar fixtures; o seeder recusa ambientes diferentes de `local/testing`.

## Comandos úteis

```powershell
php artisan serve
npm run dev
php artisan migrate
php artisan db:seed
php artisan route:list --except-vendor
php artisan test --compact
npm run build
git status --short
git diff --check
```

Caso o banco ainda não exista e a confirmação interativa falhe no caminho acentuado, `php artisan migrate --force` contornou o prompt nesta máquina. Os arquivos citados no erro original existiam; a causa de encoding foi inferida do caminho corrompido e do sucesso sem interação, não isolada por investigação do runtime.

Para instalação nova, usar Composer/npm e `.env.example`, gerar chave e criar o vínculo com `php artisan storage:link`. Não gerar outra chave para uma instalação existente sem motivo. O script `composer dev` inclui Pail; compatibilidade de execução desse conjunto no Windows não foi validada nesta revisão.

## Histórico

### 2026-10-15 — Banco e migrations — concluído

- Antes: banco `anime_calendar` ausente; `migrate` falhava no prompt com erros de include e caminho acentuado corrompido.
- Ação: verificados PHP/dependências; executado `migrate --force` e depois `migrate` normal.
- Depois: 17 migrations aplicadas e nenhuma pendente. Sem edição de código nessa etapa.

### 2026-10-15 — Fotos e banners — concluído

- Antes: accessors em `app/Models/User.php` montavam `asset('public/storage/...')`, retornando 404 no servidor local.
- Alteração: removido `public/` nos accessors de foto/banner; views consumidoras recebem `/storage/...`.
- Validação: lint e script verificando ambas as URLs e atributos vazios; junction já existia.

### 2026-10-15 — Dados para testes — concluído

- Antes: um usuário existente e catálogo vazio.
- Alteração: criado `database/seeders/DemoSeeder.php`; `DatabaseSeeder.php` passa a chamá-lo. Promoção do primeiro usuário, contas demo, perfis com SVGs, catálogo fictício, planos globais/pessoais, dias/logs e metadados.
- Validação: execução real no MySQL local e repetição sem crescimento de contagens; verificação de senha preservada, login demo e existência dos arquivos.
- Limites: dados sintéticos; reexecução preserva parte dos registros, mas redefine perfis demo conforme P23.

### 2026-10-15 — Revisão e continuidade — concluído

- Pedido: analisar o projeto e manter memória de estado, alterações e tarefas entre sessões.
- Alteração: criados `AGENTS.md` e este diário com mapa, baseline, histórico, backlog priorizado e verificações.
- Validação: build, lint, rotas, compilação Blade e suíte existente; falha P05 registrada sem ocultá-la.
- Estado final: documentação pronta; problemas P01–P24 pendentes. Nenhuma correção adicional de domínio foi feita nesta etapa. Nenhum deploy/push/commit.
- Próximo passo: autorização e isolamento global/pessoal, seguido de correção de username e testes de progresso.

### Modelo para próximas entradas

```text
AAAA-MM-DD — Tarefa — status
Pedido:
Antes:
Alterações e arquivos:
Validação (comandos e resultado):
Pendências / riscos concretos:
Próximo passo:
```

## Resolução da revisão após a reformulação

| Itens | Estado atual | Implementação / limite |
|---|---|---|
| P01–P04 | Concluídos | Manutenção HTTP removida; escrita global e sinopse exigem admin; confirmação consulta o proprietário correto |
| P05–P07 | Concluídos | Username gerado no model e fallback legado, importação pessoal liberada, Policy de cópia respeita visibilidade, rotas admin rejeitam planos pessoais |
| P08–P10 | Concluídos | Intervalos de pausa, conclusão serializada/idempotente, projeção compartilhada e GET sem persistência |
| P11 | Concluído | FK passa a cascadeOnDelete, impedindo promoção de planos privados a globais |
| P12 | Concluído | Escopo de reviews públicas, média SQL filtrada, comentários privados suprimidos, seção própria de avaliações; proprietário vê seus dados |
| P13–P15 | Concluídos | Unicidade no banco, locks/transações para agenda/ranking/logs, enum e limites/datas validados |
| P16 | Concluído | Timeout, connect timeout, cache de 10 minutos, validação da resposta e mensagem de indisponibilidade; sem retries automáticos em 429 |
| P17 | Concluído | PlanProjection/PlanManager substituem regras duplicadas nos controllers e model |
| P18 | Implementado; benchmark futuro | Paginação SQL, eager loading, médias/última review no banco, janela de histórico; teste garante limite de queries no índice pessoal. Logs necessários à projeção ainda são carregados por plano; não houve teste de carga |
| P19 | Concluído no fluxo normal | Upload novo antes da gravação, lock da conta, limpeza após commit e compensação de falha; exclusão limpa arquivos e cascade de planos. Falhas físicas de exclusão de arquivos podem exigir limpeza operacional |
| P20 | Decisão concluída | Verificação de e-mail permanece opcional; removido middleware verified inefetivo do dashboard |
| P21 | Parcial por decisão | Tema escuro coerente e APP_TIMEZONE configurável com padrão America/Sao_Paulo; localização completa e QA visual ficam como melhorias futuras |
| P22–P23 | Concluídos | README refeito, arquivo vazio removido, sprite movido para public/images/warrior-sprite.png, import inexistente removido; capas demo recriadas na reexecução e seeds usam pausas/conclusão novas |
| P24 | Base concluída; expansão futura | Suíte ampliada, Pint e CI configurados; removido pacote Tailwind v4 não utilizado. E2E/browser, teste de carga e execução remota de CI não foram realizados |

### 2026-10-15 — Reformulação modular — concluído

- **Pedido:** traçar e executar uma reformulação profissional, modularizar, otimizar e permitir reescrita ampla.
- **Antes:** controllers com centenas de linhas e regras duplicadas, telas repetidas, falhas de autorização e integridade, suíte original com uma falha; baseline documentada acima.
- **Plano:** PLANO_DE_REFORMULACAO.md; monólito modular Laravel com Eloquent, Requests, Policy, query objects, serviços e componentes Blade. Nenhuma camada de repository duplicando Eloquent foi adicionada.
- **Planejamento:** app/Services/Planning/PlanProjection.php centraliza cálculo, calendário e histórico. Progresso usa semanas mais exceções, não um loop completo por dia. Janela de calendário é de um mês; histórico é filtrável até 366 dias, padrão de 90 dias.
- **Escrita:** PlanManager salva agenda atomicamente, serializa operações, registra pausas e impede que conclusão repetida duplique logs. Copiar plano preserva um plano próprio já existente. Remover plano preserva avaliações/favoritos (mudança intencional da UX).
- **HTTP e acesso:** routes/web.php compõe catalog/profiles/planning/admin/auth. Controllers de contexto usam Planning/PlanController para orquestração comum; LibraryController concentra avaliações/favoritos/ranking. WatchPlanPolicy controla mutação e cópia; Requests validam entradas. Toda escrita global exige admin.
- **Dados e perfil:** ProfileQuery/AnimeCatalogQuery/WatchPlanQuery fornecem leituras paginadas. ProfileManager salva arquivos novos antes do banco e remove antigos após commit. Perfis privados são acessíveis ao proprietário. Cadastro usa geração de username do model, limitada a 29 caracteres; migração completa usernames legados sem apagar nomes existentes.
- **Interface:** templates planning/form, index, log, calendar, history e completed compartilhados; componentes plan-card, profile-anime-card, anime-import-form e validation-summary. Perfil refeito com listas paginadas, avaliações e controles de favorito/Top 10. Templates antigos de entrada viraram wrappers para preservar nomes de views e rotas.
- **Cliente externo:** JikanService aplica timeout/cache/checagem de resposta; busca mostra indisponibilidade sem 500. Testes usam fake; nenhuma chamada real de busca foi necessária.
- **Integridade:** três migrations incrementais adicionam owner_key gerado, índices únicos para planos/metadados/dias/logs, FK pessoal por cascata, tabela watch_plan_pauses, completed_at e preenchimento legado. Duplicatas abortam a migração; não são apagadas.
- **Compatibilidade histórica:** pausas antigas não tinham data registrada; foram congeladas a partir da data da migração. Conclusões antigas usam updated_at como melhor limite disponível. Não é possível reconstruir com exatidão intervalos que nunca foram persistidos.
- **Backup:** schema e linhas do MySQL exportados antes das migrations para storage/app/private/backups/before-refactor-20261015-233202.json. Arquivo privado e ignorado pelo Git. Continha 21 usuários, 16 animes, 217 planos e 211 metadados; credenciais pessoais não foram alteradas.
- **Banco após migração:** 20 migrations aplicadas, 63 intervalos de pausa legados, mesmas contas/animes/planos. Nenhum migrate:fresh/db:wipe/truncate. Testes automatizados permanecem em SQLite em memória.
- **Manutenção:** formatação Laravel Pint; composer format/format:check/check; CI em .github/workflows/checks.yml; README refeito. Composer dev não inclui Pail, para uso no Windows. @tailwindcss/vite não utilizado removido do package.json/lock e node_modules.
- **Verificações:** 54 testes passaram; detalhes finais de assertions/build/formatação abaixo. Composer JSON validado. Checagem de 71 ações de controller sem métodos ausentes. Smoke test pelo kernel HTTP em 21 páginas do MySQL retornou 200; snapshot dos planos antes/depois confirmou leitura sem mudança. Catálogo usou 7 queries, calendário/histórico 7 e perfil demo 21 no cenário observado (medição local, não benchmark).
- **Limites:** CI foi criada, não executada no GitHub; não houve deploy, push ou commit. Não houve auditoria de dependências completa online, teste de carga nem QA visual em navegador. Aviso de Browserslist antigo pode continuar no build. Autenticação Breeze foi preservada; verificação obrigatória de e-mail não foi ativada.
- **Próximo passo:** testar visualmente os fluxos com usuário comum/admin e revisar acessibilidade se solicitado. A reformulação funcional desta etapa está concluída; manter este diário atualizado em mudanças futuras.

### Verificação final da reformulação

- Pest/PHPUnit: **54 testes aprovados, 173 assertions**; SQLite em memória, sem rede nos testes Jikan.
- Laravel Pint `--test`: aprovado; PHP lint: **105 arquivos** sem erros de sintaxe.
- `npm run build`: aprovado (54 módulos, CSS 66,09 kB e JS 83,33 kB antes de gzip); apenas aviso de Browserslist desatualizado.
- `php artisan view:cache`: aprovado; views compiladas foram limpas ao encerrar, para o desenvolvimento continuar normalmente.
- `composer validate --no-check-publish`: aprovado; `git diff --check`: aprovado.
- MySQL: **20 migrations aplicadas**, incluindo as três incrementais desta reformulação.
- O arquivo de sprite foi movido, não descartado. Alterações cosméticas nas migrations antigas são resultado do Pint; a evolução do schema foi feita exclusivamente nas novas migrations.

### 2026-10-15 — Pretendo assistir — concluído

- **Pedido:** permitir guardar animes para assistir depois e distribuir a ação nas telas.
- **Antes:** somente assistindo, pausado e concluído; qualquer plano novo normalmente iniciava acompanhamento.
- **Domínio:** WatchStatus::Planned com valor pretendo_assistir e rótulos centralizados. PlanManager::saveForLater cria uma entrada pessoal sem agenda/logs, com transação e lock por anime. Repetições não duplicam nem alteram planos existentes. PlanProjection ignora essa categoria no progresso, calendário e histórico; logs são recusados até o início.
- **Início:** ação Começar a assistir reutiliza a rota pessoal de retomada, respeita proprietário, começa na data atual e cria agenda padrão de 1 episódio/dia somente se ainda não houver configuração. Editar permite escolher agenda/status. Conclusão direta também reinicia a referência de datas, sem contabilizar o tempo de espera. Planos com progresso registrado devem usar Pausado em vez de serem convertidos para espera.
- **HTTP:** LibraryController adiciona listagem e gravação em GET /me/watch-later e POST /me/watch-later/{anime}, ambas autenticadas. Importação Jikan aceita target=later e salva diretamente, sem formulário de agenda; importação de anime já acompanhado mantém o status e direciona à biblioteca.
- **Interface:** ação no catálogo, busca Jikan, detalhes, cards de perfis/comunidade; acesso no dashboard e menus desktop/mobile. Biblioteca tem seção própria paginada, e /me/watch-later tem estado vazio e ações de iniciar/editar/remover. Cards não mostram controles de log/pausa para itens em espera. Catálogo distingue espera, pausa e acompanhamento.
- **Privacidade:** lista de espera é pessoal; aparece no perfil apenas para o próprio usuário. Nenhum campo de privacidade novo foi presumido para exposição pública.
- **Banco:** sem migration, pois watch_status já é varchar. Não houve reset, reseed ou alteração dos registros locais para demonstrar a funcionalidade.
- **Arquivos:** enum, PlanManager/PlanProjection, WatchPlanQuery/ProfileQuery, controllers Library/Anime/Planning, ImportAnimeRequest, routes/planning.php, componentes watch-later-button/plan-card/anime-import-form/profile-anime-card, templates de biblioteca/catálogo/detalhes/busca/perfil/navegação/dashboard e novo personal/watch-later.blade.php. README atualizado.
- **Validação:** 65 testes passaram com 256 assertions (11 testes novos de espera, incluindo repetição, isolamento, privacidade, importação, início, agenda, conclusão e remoção). Pint aplicado; build aprovado; compilação Blade e git diff --check aprovados. Smoke HTTP de seis páginas no MySQL retornou 200 e snapshot confirmou que as leituras preservaram os planos. Aviso preexistente de Browserslist antigo permanece.
- **Próximo passo:** nenhum bloqueio para usar a funcionalidade. QA visual em navegador continua como melhoria de validação futura; não foi executado nesta etapa.

### 2026-10-15 — Começar a assistir abre planejamento — concluído

- **Pedido:** ao clicar em Começar a assistir, abrir a tela para fazer um plano.
- **Antes:** cards da lista, biblioteca e perfil enviavam PATCH de retomada e iniciavam uma agenda automaticamente. Catálogo e detalhes já abriam o formulário.
- **Alteração:** plan-card agora navega para a criação de plano do contexto pessoal/global. A abertura é somente leitura e mantém Pretendo assistir. O controller preenche dias já configurados, quando houver. O salvamento reutiliza a entrada existente e respeita a data escolhida, incluindo início futuro, sem duplicar o anime. Texto de início rápido substituído por orientação para configurar e salvar.
- **Arquivos:** components/plan-card, planning/form, personal/watch-later, Planning/PlanController, Planning/PlanManager, README e WatchLaterTest.
- **Validação:** 66 testes aprovados, 273 assertions; regressão confirma link do card, formulário sem alteração de status, dados de agenda preservados, validação sem início indevido e salvamento com data futura. Pint aplicado e git diff --check aprovado.
- **Banco:** nenhuma migration, reset, seed ou alteração dos dados locais. A descrição do início rápido na entrada anterior é histórica e foi substituída por este fluxo na interface.
- **Próximo passo:** nenhum pendente para este pedido.

### 2026-10-15 — Trazer main e alterações recentes para dev — concluído

- **Pedido:** trazer tudo que estava na main para a dev, incluindo o trabalho guardado antes da troca de branch.
- **Antes:** dev em a93b5c1; main em 109d3f7; reformulação e alterações recentes protegidas em stash.
- **Ação:** git merge --ff-only main na dev e git stash apply do stash identificado como reformulação/pretendo assistir. Aplicação sem conflitos; stash mantido como cópia de segurança.
- **Validação:** dev e main no mesmo commit, divergência 0/0; arquivos rastreados idênticos ao stash e hashes de todos os arquivos novos conferidos, sem divergência. Nenhum conflito pendente.
- **Estado:** trabalho restaurado na dev como alterações locais ainda sem commit. Nenhum push, reset ou alteração no banco. Este registro foi acrescentado após conferir a restauração.

### 2026-10-15 — Commit da reformulação na dev

- **Pedido:** criar commit do trabalho restaurado na dev.
- **Conteúdo:** reformulação modular, correções de permissões/progresso, seeds de teste, Pretendo assistir, início via formulário, documentação e CI.
- **Verificação:** diff sem erros de whitespace; conteúdo restaurado conferido com o stash na etapa anterior. A última validação funcional foi de 66 testes aprovados e 273 assertions; não houve mudança funcional depois dela.
- **Mensagem:** refactor: modulariza aplicacao e adiciona lista para assistir depois.
- **Escopo:** commit local na dev; sem push. Backup privado, .env, vendor, node_modules e assets de build permanecem ignorados.
