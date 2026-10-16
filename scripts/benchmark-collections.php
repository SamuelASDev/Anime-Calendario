<?php

// Explicitly isolated benchmark. No .env database is migrated or seeded.
use App\Models\User;
use App\Services\Library\CollectionOrder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'database.connections.sqlite.url' => null, 'cache.default' => 'array']);
DB::purge('sqlite');
if (DB::connection()->getDatabaseName() !== ':memory:') {
    throw new RuntimeException('Benchmark requires isolated SQLite memory database.');
}
Artisan::call('migrate', ['--force' => true]);
$queries = [];
$recording = false;
DB::listen(function ($query) use (&$queries, &$recording) {
    if ($recording) {
        $queries[] = $query->sql;
    }
});
$report = ['database' => 'SQLite :memory:', 'php' => PHP_VERSION, 'samples' => []];
foreach ([100, 1000, 10000] as $size) {
    $user = User::factory()->create();
    $offset = (int) DB::table('animes')->max('id');
    foreach (array_chunk(range(1, $size), 250) as $chunk) {
        $animes = $meta = $plans = [];
        foreach ($chunk as $index) {
            $id = $offset + $index;
            $animes[] = ['id' => $id, 'mal_id' => $id, 'title' => 'Benchmark '.$id];
            $meta[] = ['user_id' => $user->id, 'anime_id' => $id, 'is_favorite' => true];
            $plans[] = ['user_id' => $user->id, 'anime_id' => $id, 'watch_status' => 'concluido', 'start_date' => '2026-01-01'];
        }
        DB::table('animes')->insert($animes);
        DB::table('user_anime_meta')->insert($meta);
        DB::table('watch_plans')->insert($plans);
    }
    foreach (['favorites', 'completed'] as $collection) {
        $durations = $writes = $counts = [];
        foreach (range(1, 20) as $iteration) {
            $queries = [];
            $recording = true;
            $start = hrtime(true);
            app(CollectionOrder::class)->move($user, $offset + intdiv($size, 2), $collection, $iteration % 2 ? 'up' : 'down');
            $durations[] = (hrtime(true) - $start) / 1e6;
            $recording = false;
            $writes[] = count(array_filter($queries, fn ($sql) => preg_match('/^(update|insert|delete) /i', $sql)));
            $counts[] = count($queries);
        }
        sort($durations);
        $report['samples'][] = ['collection' => $collection, 'size' => $size, 'iterations' => 20,
            'median_ms' => round($durations[9], 3), 'p95_ms' => round($durations[18], 3),
            'max_queries' => max($counts), 'max_writes' => max($writes)];
        if (max($writes) !== 2) {
            throw new RuntimeException('Ordering write count changed.');
        }
    }
}
$report['peak_memory_mb'] = round(memory_get_peak_usage(true) / 1048576, 2);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;
