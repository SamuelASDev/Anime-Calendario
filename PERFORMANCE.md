# Medições das coleções

Execução local em 15/10/2026, PHP 8.2.12, SQLite em memória. Comando: `php scripts/benchmark-collections.php`. O script cria seu próprio banco e não migra o MySQL configurado no `.env`. São 20 movimentos alternados por cenário; a criação das fixtures fica fora do tempo medido.

| Coleção | Itens | Mediana | P95 | Consultas máximas | Gravações máximas |
|---|---:|---:|---:|---:|---:|
| Favoritos | 100 | 1,059 ms | 1,379 ms | 7 | 2 |
| Concluídos | 100 | 1,129 ms | 1,184 ms | 7 | 2 |
| Favoritos | 1.000 | 1,161 ms | 1,251 ms | 7 | 2 |
| Concluídos | 1.000 | 1,581 ms | 1,646 ms | 7 | 2 |
| Favoritos | 10.000 | 2,089 ms | 2,928 ms | 7 | 2 |
| Concluídos | 10.000 | 6,842 ms | 7,360 ms | 7 | 2 |

Pico de memória do processo completo: 32 MB. Estes números não estimam latência HTTP, concorrência ou desempenho de produção no MySQL. O número de gravações é a propriedade protegida pelo benchmark; tempos variam entre máquinas e execuções. A consulta do vizinho ainda precisa ordenar/avaliar registros; não é uma operação de tempo constante.

Antes, cada movimento carregava todos os IDs e regravava todas as posições. Agora são lidos o item e seu vizinho e trocados somente os dois valores, sob o mesmo bloqueio transacional por usuário. Posições virtuais estáveis para registros ainda não ordenados evitam inicialização em massa; BIGINT comporta esses valores e as posições existentes continuam válidas. Top 10 permanece limitado a dez itens.

As respostas assíncronas deixam de retornar o layout completo: a mutação retorna os nomes dos blocos e a leitura seguinte traz apenas seus fragmentos. Os testes de navegador verificam a lista exata de fragmentos, payload menor que a página completa e ausência de navegação durante a ordenação.

A CI executa o benchmark e publica `collection-benchmark.json`. Os testes de navegador ficam em `tests/Browser/profile.spec.js`, com banco/servidor próprios. Próximas medições de produção devem usar uma base descartável MySQL e cobrir usuários simultâneos, tamanho das respostas e duração das consultas sob carga.
