# Reformulação do Anime Calendar

## Objetivo e arquitetura

Construir um monólito modular Laravel: controllers HTTP pequenos, Form Requests para entrada, Policies para autorização, serviços de domínio para regras e escritas transacionais, query objects para leitura e componentes Blade para apresentação. Eloquent continua sendo a camada de persistência; não criar repositories que apenas repitam sua API. Preservar URLs, contas e dados existentes com migrations incrementais.

Módulos: Catálogo, Planejamento, Biblioteca pessoal/avaliações e Perfis. Cada módulo tem regras explícitas; o módulo de planejamento fornece uma única projeção para calendário, histórico e progresso. GET não altera dados. Planos globais pertencem à administração; planos pessoais pertencem ao usuário. Visibilidade é verificada no servidor.

## Execução

1. **Fundação e proteção:** policies, escopos de consulta, rotas por módulo, remoção da manutenção HTTP, tratamento de username legado. Testes de autorização antes de avançar.
2. **Domínio de planejamento:** serviço único de projeção, ações transacionais de agenda/log/pausa/conclusão/cópia, registro de intervalos de pausa, status centralizados e requisições validadas.
3. **Integridade:** backup privado do banco local, migrations não destrutivas, unicidade de planos/metadados/dias/logs e exclusão de conta sem transformar planos pessoais em globais. Abortar diante de duplicatas em vez de apagá-las silenciosamente.
4. **Catálogo e biblioteca:** importação pessoal disponível, reviews com visibilidade consistente, ranking transacional, cliente Jikan com timeout/cache/erros tratados e testes sem rede.
5. **Leitura e apresentação:** paginação SQL, eager loading, agregações, componentes para calendário/histórico/formulários, mensagens de validação e identidade visual coerentes.
6. **Entrega e manutenção:** suite de regressão, lint/formatação/build, CI, README de instalação/arquitetura e diário atualizado com evidências e limites.

## Critérios de aceite

- Nenhum usuário altera planos alheios ou globais sem permissão; nenhum GET executa manutenção ou conclui planos.
- Pausa não avança episódios; retomada preserva histórico; conclusão é idempotente inclusive hoje; logs substituem previsão.
- Controllers delegam as regras compartilhadas; calendário e histórico usam o mesmo motor.
- Banco local preservado; índices impedem duplicidade; testes usam SQLite em memória e exercitam migrations novas.
- Busca externa falha de forma compreensível; privacidade é aplicada nas consultas.
- Build e testes passam; documentação explica execução, padrões e pendências concretas.

## Decisões de produto desta implementação

- Continuar avanço automático pela agenda, com precedência dos logs e dias variáveis sem avanço.
- Manter perfis e catálogo acessíveis a usuários autenticados; o termo público significa visível à comunidade.
- Admin gerencia planos globais; dados pessoais continuam isolados inclusive nas rotas administrativas.
- Copiar agenda pública é permitido; copiar planos privados de terceiros é proibido e nunca substitui silenciosamente um plano pessoal existente.
- Revisões só podem ser escritas para animes concluídos na biblioteca pessoal; privacidade de reviews vale nas telas comunitárias.
- Manter verificação de e-mail opcional por enquanto, sem adicionar bloqueio de acesso às contas locais.

O estado real da execução e as verificações ficam no `DIARIO_DO_PROJETO.md`.

## Estado de entrega

As seis etapas foram implementadas nesta sessão: fundação/autorização, domínio de planejamento, integridade com backup e migrations locais, catálogo/biblioteca, apresentação compartilhada e automação/documentação. Preservadas as URLs de negócio; a rota pública de manutenção foi removida.

A suíte de regressão e a validação HTTP local estão registradas no diário. QA visual em navegador, benchmark com alto volume, localização completa e execução da CI no remoto ficam como evolução posterior, sem afirmar validação que não foi executada.
