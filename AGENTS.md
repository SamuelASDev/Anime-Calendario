# Continuidade do projeto

Antes de começar uma tarefa, leia `DIARIO_DO_PROJETO.md` na raiz e confira o estado real com `git status --short`. O diário contém o contexto, a revisão inicial, os problemas conhecidos e o ponto de retomada; confirme informações que possam ter mudado.

Ao terminar uma tarefa relevante, atualize o diário nesta mesma alteração:

- Registre data, pedido, estado anterior, alterações e arquivos envolvidos.
- Registre as verificações executadas e seus resultados reais, inclusive falhas.
- Marque o que foi concluído e o que ficou pendente; indique o próximo passo concreto.
- Preserve o histórico. Não apresente sugestões como alterações implementadas.
- Não copie segredos do `.env`, senhas pessoais, tokens ou dados sensíveis para a documentação.

O projeto possui dados locais de teste e uma conta original que deve ser preservada. Não execute `migrate:fresh`, `db:wipe` ou apague dados para validar uma alteração comum. A suíte está configurada para SQLite em memória em `phpunit.xml`; confirme o isolamento antes de usar `RefreshDatabase`.

Planos globais usam `watch_plans.user_id = null`; planos pessoais usam o ID do proprietário. Preserve essa separação e verifique autorização no servidor ao alterar esses fluxos.

Use UTF-8 nos arquivos. No Windows, caminhos com acentos podem aparecer corrompidos em saídas de console; confira a leitura em UTF-8 antes de regravar arquivos ou concluir que o conteúdo está corrompido.
