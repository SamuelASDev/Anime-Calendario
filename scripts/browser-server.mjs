import { mkdirSync, writeFileSync, unlinkSync } from 'node:fs';
import { resolve } from 'node:path';
import { randomBytes } from 'node:crypto';
import { spawn, spawnSync } from 'node:child_process';

// A new, isolated SQLite database per run; never use .env's MySQL connection.
const directory = resolve('storage/framework/testing');
mkdirSync(directory, { recursive: true });
const database = resolve(directory, `browser-${process.pid}-${Date.now()}.sqlite`);
writeFileSync(database, '');
const env = {
    ...process.env,
    APP_ENV: 'testing', APP_DEBUG: 'false', APP_KEY: `base64:${randomBytes(32).toString('base64')}`,
    APP_CONFIG_CACHE: resolve(directory, 'browser-no-config-cache.php'),
    DB_CONNECTION: 'sqlite', DB_DATABASE: database, DB_URL: '',
    CACHE_STORE: 'array', SESSION_DRIVER: 'file', QUEUE_CONNECTION: 'sync', MAIL_MAILER: 'array',
};
for (const args of [['migrate', '--force'], ['db:seed', '--class=BrowserTestSeeder', '--force']]) {
    const result = spawnSync('php', ['artisan', ...args], { env, stdio: 'inherit' });
    if (result.status !== 0) {
        unlinkSync(database);
        process.exit(result.status || 1);
    }
}
const server = spawn('php', ['artisan', 'serve', '--host=127.0.0.1', `--port=${process.env.E2E_PORT || 8011}`, '--no-reload'], { env, stdio: 'inherit' });
for (const signal of ['SIGINT', 'SIGTERM']) process.on(signal, () => server.kill(signal));
server.on('exit', (code) => {
    try { unlinkSync(database); } catch { /* Server shutdown may still hold the file on Windows. */ }
    process.exit(code || 0);
});
