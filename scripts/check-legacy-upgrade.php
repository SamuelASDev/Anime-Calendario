<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$connection = config('database.connections.mysql');
if (! in_array($connection['host'], ['localhost', '127.0.0.1'], true) || ! empty($connection['url'])) {
    throw new RuntimeException('Run this check only against a disposable local database server.');
}
$database = 'anime_upgrade_'.bin2hex(random_bytes(6));
$connection['database'] = null;
config(['database.connections.upgrade_admin' => $connection]);
$admin = DB::connection('upgrade_admin');
$admin->statement("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
try {
    $connection['database'] = $database;
    config(['database.connections.upgrade_check' => $connection, 'database.default' => 'upgrade_check', 'cache.default' => 'array']);
    $legacy = array_values(array_filter(glob(__DIR__.'/../database/migrations/*.php'), fn ($path) => basename($path) < '2026_10_15'));
    $migrate = function (array $options = []) {
        if (Artisan::call('migrate', $options + ['--database' => 'upgrade_check', '--force' => true]) !== 0) {
            throw new RuntimeException(Artisan::output());
        }
    };
    $migrate(['--path' => $legacy, '--realpath' => true]);
    $user = DB::table('users')->insertGetId(['name' => 'Legacy user', 'email' => 'legacy@example.test', 'password' => 'fixture']);
    $anime = DB::table('animes')->insertGetId(['mal_id' => 20, 'title' => 'Legacy anime']);
    $base = ['anime_id' => $anime, 'start_date' => '2026-01-01', 'episodes_watched' => 3, 'episodes_per_day' => 1, 'watch_status' => 'assistindo'];
    $global = DB::table('watch_plans')->insertGetId($base + ['user_id' => null]);
    $personal = DB::table('watch_plans')->insertGetId($base + ['user_id' => $user]);
    $before = DB::table('watch_plans')->orderBy('id')->get()->map(fn ($row) => (array) $row)->all();
    $migrate();
    foreach ($before as $row) {
        $after = (array) DB::table('watch_plans')->find($row['id']);
        if (array_intersect_key($after, $row) !== $row) {
            throw new RuntimeException('Legacy data changed during upgrade.');
        }
    }
    foreach ([null, $user] as $owner) {
        try {
            DB::table('watch_plans')->insert($base + ['user_id' => $owner]);
            throw new RuntimeException('Owner uniqueness was not enforced.');
        } catch (QueryException $exception) {
            if ((string) $exception->getCode() !== '23000') {
                throw $exception;
            }
        }
    }
    // Exercise down/up on the affected migration before restoring SET NULL.
    $migration = require __DIR__.'/../database/migrations/2026_10_15_000001_harden_watch_plan_integrity.php';
    $migration->down();
    if (Schema::hasColumn('watch_plans', 'owner_key')) {
        throw new RuntimeException('Generated column was not removed before restoring the legacy key.');
    }
    $migration->up();
    DB::table('users')->where('id', $user)->delete();
    if (DB::table('watch_plans')->where('id', $personal)->exists() || ! DB::table('watch_plans')->where('id', $global)->exists()) {
        throw new RuntimeException('Personal/global delete behavior changed.');
    }
    echo 'PASS: legacy upgrade, data preservation, owner uniqueness, down/up and cascade on '.DB::selectOne('SELECT VERSION() AS version')->version.PHP_EOL;
} finally {
    DB::disconnect('upgrade_check');
    $admin->statement("DROP DATABASE `{$database}`");
}
