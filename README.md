<div align="center">
  <img src="public/images/logoSapo.png" alt="Logo do Anime Tracker" width="112">

  <h1>Anime Tracker</h1>

  <p>Organize sua biblioteca, planeje seus episódios e acompanhe sua evolução.</p>
  <p><strong>Um episódio de cada vez.</strong></p>

  <p>
    <a href="https://github.com/SamuelASDev/Anime-Calendario/actions/workflows/checks.yml"><img src="https://github.com/SamuelASDev/Anime-Calendario/actions/workflows/checks.yml/badge.svg" alt="Status da integração contínua"></a>
    <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP 8.2 ou superior">
    <img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel 12">
    <img src="https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS 3">
  </p>

  <p>
    <a href="#funcionalidades">Funcionalidades</a> ·
    <a href="#instalação">Instalação</a> ·
    <a href="#arquitetura">Arquitetura</a> ·
    <a href="#testes-e-qualidade">Testes</a> ·
    <a href="#autor">Autor</a>
  </p>
</div>

## Sobre o projeto

O **Anime Tracker** é uma aplicação web para transformar sua lista de animes em uma rotina de acompanhamento. Reúna os títulos que pretende assistir, monte uma agenda semanal e registre os episódios vistos, com calendário, histórico e progresso em um só lugar.

Além da biblioteca pessoal, a plataforma oferece uma comunidade autenticada com perfis, avaliações, favoritos, Top 10 e agendas compartilhadas pela administração. A interface utiliza tema escuro, capas em destaque e layouts responsivos.

## Funcionalidades

| Área | O que você pode fazer |
| --- | --- |
| **Catálogo** | Buscar animes na integração externa, importar títulos e consultar detalhes, capas, sinopses e gêneros. |
| **Biblioteca pessoal** | Organizar títulos em Pretendo assistir, Assistindo, Pausado e Concluído. |
| **Planejamento** | Definir a data de início, episódios já assistidos e uma agenda por dia da semana, incluindo dias de ritmo variável. |
| **Acompanhamento** | Consultar progresso estimado, registrar episódios reais, pausar, retomar e concluir animes. |
| **Calendário e histórico** | Visualizar a programação do próximo mês e consultar períodos de até 366 dias no histórico. |
| **Coleções** | Avaliar animes concluídos, escrever comentários, marcar favoritos e organizar seu Top 10. |
| **Perfil** | Personalizar avatar, banner e biografia, com controles de visibilidade das coleções. |
| **Comunidade** | Descobrir pessoas, consultar coleções públicas e copiar agendas cuja visibilidade permita o acesso. |
| **Administração** | Gerenciar os planos globais da comunidade e editar sinopses do catálogo. |

As listagens oferecem filtros e paginação. No perfil, ações de favoritos e ordenação atualizam os blocos afetados sem recarregar a página inteira.

### Como funciona o progresso

- A agenda semanal gera uma previsão a partir da data de início do plano.
- Um registro real substitui a previsão daquele dia, inclusive quando nenhum episódio foi assistido.
- Dias variáveis sem registro não avançam o progresso automaticamente.
- Intervalos de pausa interrompem o avanço previsto.
- Títulos em **Pretendo assistir** só iniciam o acompanhamento depois que o planejamento é configurado e salvo.
- A conclusão é uma ação explícita; consultar uma tela não altera o status no banco.

## Tecnologias

| Camada | Tecnologias |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12, Eloquent e Laravel Breeze |
| Interface | Blade, Alpine.js e Tailwind CSS 3 |
| Build | Vite 7 e npm |
| Banco de dados | MySQL; SQLite para testes isolados |
| Catálogo externo | Adaptador Tenrai com identificadores MyAnimeList |
| Qualidade | Pest, PHPUnit, Laravel Pint e Playwright |
| Integração contínua | GitHub Actions, com verificações de aplicação e atualização de esquema em MySQL/MariaDB |

## Instalação

### Requisitos

- PHP **8.2 ou superior**, Composer 2 e as extensões exigidas pelo Laravel.
- Extensões `pdo_mysql`, `pdo_sqlite`, `mbstring` e `fileinfo` habilitadas.
- MySQL disponível e um banco criado para a aplicação.
- Node.js **20.19+ ou 22.12+**, com npm.
- Git.

### 1. Obtenha o código e instale as dependências

```bash
git clone https://github.com/SamuelASDev/Anime-Calendario.git
cd Anime-Calendario
composer install
npm ci
```

### 2. Configure o ambiente

Copie o arquivo de exemplo:

```bash
cp .env.example .env
```

No PowerShell, use `Copy-Item .env.example .env`.

Ajuste o `.env` com a conexão do seu banco. Exemplo de configuração local:

```dotenv
APP_NAME="Anime Tracker"
APP_URL=http://127.0.0.1:8000
APP_TIMEZONE=America/Sao_Paulo

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=anime_calendar
DB_USERNAME=seu_usuario
DB_PASSWORD=sua_senha
```

Gere a chave **somente em uma instalação nova**, aplique as migrations e disponibilize os arquivos públicos:

```bash
php artisan key:generate
php artisan migrate
php artisan storage:link
```

> Ao atualizar uma instalação existente, preserve o `.env`, a `APP_KEY` e os dados. Utilize migrations incrementais; não execute `migrate:fresh` ou `db:wipe` para atualizar a aplicação.

### 3. Inicie a aplicação

Para desenvolvimento, execute:

```bash
composer dev
```

Esse comando inicia o servidor Laravel, o listener de filas e o Vite. Acesse **http://127.0.0.1:8000**.

Para executar apenas o servidor com os assets compilados:

```bash
npm run build
php artisan serve
```

### Dados de demonstração — opcionais

Em ambiente `local` ou `testing`, você pode popular a aplicação com catálogo fictício, imagens locais, perfis e coleções:

```bash
php artisan db:seed
```

O seeder cria a conta `admin@anime-calendar.test` e contas de demonstração de `usuario01@anime-calendar.test` a `usuario19@anime-calendar.test`. A senha de demonstração está definida em [DemoSeeder.php](database/seeders/DemoSeeder.php).

> O seeder também promove o primeiro usuário existente a administrador e redefine os perfis de demonstração ao ser reexecutado. Utilize-o em uma instalação de desenvolvimento dedicada. As contas de demonstração não se destinam à produção.

## Arquitetura

O projeto segue uma arquitetura de **monólito modular**, com responsabilidades separadas e componentes compartilhados entre os contextos pessoal e global.

```text
app/
├── Contracts/           # Contratos de integração externa
├── Data/                # Dados normalizados do catálogo
├── Enums/               # Estados do domínio
├── Http/
│   ├── Controllers/     # Coordenação dos fluxos HTTP
│   ├── Middleware/      # Controle de acesso administrativo
│   └── Requests/        # Validação de entradas
├── Models/              # Entidades e relacionamentos Eloquent
├── Policies/            # Autorização de planos e cópias
├── Queries/             # Filtros, paginação e consultas
└── Services/
    ├── Catalog/         # Adaptador do catálogo externo
    ├── Library/         # Ranking e ordenação de coleções
    ├── Planning/        # Agenda, projeção, logs e transições
    └── Profiles/        # Perfil e ciclo de vida de uploads
resources/
├── css/                 # Estilos organizados por responsabilidade
├── js/                  # Interações e atualização de coleções
└── views/               # Páginas Blade e componentes reutilizáveis
routes/                  # Rotas divididas por área da aplicação
tests/
├── Browser/             # Cenários Playwright
├── Feature/             # Fluxos HTTP e regras de negócio
└── Unit/                # Testes unitários
```

O `PlanProjection` concentra o cálculo de progresso, calendário e histórico. O `PlanManager` coordena gravações transacionais de agendas, registros, pausas e conclusões. A `WatchPlanPolicy` controla as operações permitidas sobre os planos.

Planos globais possuem `user_id` nulo; planos pessoais pertencem a uma conta. Essa separação é validada no servidor. As preferências de privacidade controlam a exposição das coleções e avaliações; perfis públicos são acessíveis dentro da comunidade autenticada.

### Integração com o catálogo

O contrato `AnimeCatalogProvider` separa a aplicação do formato da API externa. O adaptador converte os resultados para `CatalogAnime`, e o serviço de busca aplica cache de dez minutos, timeout e tratamento de falhas.

O endpoint é configurável no `.env`:

```dotenv
ANIME_CATALOG_BASE_URL=https://api.tenrai.org/v1
```

Os identificadores MyAnimeList são preservados na importação. A busca externa depende da disponibilidade do provedor; os testes automatizados desse fluxo utilizam respostas simuladas.

## Testes e qualidade

Execute as verificações de backend, estilo e build:

```bash
composer check
npm run build
```

Para os testes de navegador:

```bash
npx playwright install chromium
npm run test:browser
```

- **Backend:** Pest/PHPUnit com SQLite em memória, sessão/cache em array e e-mails sem envio real.
- **Navegador:** Playwright com servidor exclusivo e banco SQLite temporário, separado do banco de desenvolvimento.
- **Estilo:** Laravel Pint.
- **CI:** [workflow Checks](.github/workflows/checks.yml), incluindo atualização de esquema legado em MariaDB 10.11 e MySQL 8.0.

Para executar o benchmark isolado de ordenação:

```bash
php scripts/benchmark-collections.php
```

O benchmark usa SQLite em memória. Metodologia, resultados e limites estão documentados em [PERFORMANCE.md](PERFORMANCE.md).

## Implantação

- Configure a raiz pública do servidor para o diretório `public/`.
- Defina `APP_ENV=production`, `APP_DEBUG=false` e o endereço correto em `APP_URL`.
- Configure banco, e-mail e serviços conforme o ambiente.
- Preserve a chave da aplicação e mantenha backups antes de atualizações.
- Disponibilize escrita em `storage/` e `bootstrap/cache/` e configure o vínculo de storage público.
- Instale as dependências, compile os assets e aplique as migrations com `php artisan migrate --force`.
- Caso utilize tarefas em fila, mantenha um worker supervisionado em execução.

## Contribuições

Sugestões e melhorias podem ser propostas pelas [issues](https://github.com/SamuelASDev/Anime-Calendario/issues) ou por pull requests. Descreva o problema, o comportamento esperado e as verificações realizadas. Antes de enviar mudanças, execute os checks correspondentes ao que foi alterado.

## Licença

Este projeto está licenciado sob a [Licença MIT](LICENSE).

Copyright (c) 2026 Samuel Abreu Sales.

## Autor

Desenvolvido por **Samuel Abreu Sales**.

[GitHub](https://github.com/SamuelASDev) · [LinkedIn](https://www.linkedin.com/in/samuel-abreu-sales-3569592b5/) · [Portfólio](https://portifolio.froglabs.site/samuel-abreu-sales-eb72dded-0fee-446e-8b31-a46abf37dc2f#projetos)
