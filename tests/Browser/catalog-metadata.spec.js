import { test, expect } from '@playwright/test';

async function login(page, email = 'browser@example.test') {
    await page.goto('/login');
    await page.getByRole('textbox', { name: 'E-mail', exact: true }).fill(email);
    await page.getByRole('textbox', { name: 'Senha', exact: true }).fill('BrowserTest@123');
    await page.getByRole('button', { name: 'Entrar', exact: true }).click();
    await expect(page).toHaveURL(/dashboard/);
}

test('catalog finds an anime by alternative name and release range', async ({ page }) => {
    await login(page);
    await page.goto('/animes');
    await page.getByRole('searchbox', { name: 'Buscar pelo título', exact: true }).fill('SnK');
    await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
    await expect(page.locator('.anime-grid article')).toHaveCount(1);
    await expect(page.locator('.anime-grid .card-title')).toHaveText('Shingeki no Kyojin');

    await page.goto('/animes');
    await page.getByLabel('Lançado a partir de').fill('2013-01-01');
    await page.getByLabel('Lançado até').fill('2013-12-31');
    await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
    await expect(page.locator('.anime-grid .card-title')).toHaveText(['Shingeki no Kyojin']);
    await expect(page.getByLabel('Lançado até')).toHaveValue('2013-12-31');

    await page.getByLabel('Lançado a partir de').fill('2014-01-01');
    await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
    await expect(page.getByText('A data final do lançamento deve ser igual ou posterior à inicial.')).toBeVisible();
});

test('filters stay inside the page on a phone', async ({ page }) => {
    await login(page);
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/animes');
    await expect(page.getByLabel('Lançado até')).toBeVisible();
    expect(await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth)).toBe(false);
});

test('follow on the home page creates a personal plan at the community episode', async ({ page }) => {
    await login(page);
    await page.getByRole('button', { name: /^Acompanhar Shingeki no Kyojin a partir do episódio \d+$/ }).click();
    await expect(page).toHaveURL(/me\/animes/);
    await expect(page.locator('.flash')).toContainText('no mesmo ponto da comunidade: episódio 5');
    await expect(page.locator('#section-assistindo .card-title')).toHaveText(['Shingeki no Kyojin']);
    await expect(page.locator('#section-assistindo')).toContainText('5');
    await page.goto('/dashboard');
    await expect(page.getByRole('link', { name: 'Na minha lista' })).toBeVisible();
    await page.goto('/me/animes');
    page.once('dialog', (dialog) => dialog.accept());
    await page.getByRole('button', { name: 'Remover Shingeki no Kyojin', exact: true }).click();
    await expect(page.locator('#section-assistindo .card-title')).toHaveCount(0);
});

test('admin runs the queue step by step, can pause, and waits when the API asks', async ({ page }) => {
    await login(page, 'admin@example.test');
    await page.goto('/admin/animes');
    await page.getByRole('link', { name: 'Completar dados' }).click();
    await expect(page.getByRole('heading', { name: 'Completar dados dos animes' })).toBeVisible();
    await expect(page.getByText('Não encontrado na API')).toBeVisible();

    const status = (synced, pending) => ({ total: 20, synced, pending, failed: 1 });
    const replies = [
        { state: 'synced', retry_after: 1, anime: 'Primeiro <b>anime</b>', status: status(3, 2) },
        { state: 'paused', retry_after: 1, status: status(3, 2) },
        { state: 'synced', retry_after: 1, anime: 'Segundo', status: status(4, 1) },
        { state: 'done', retry_after: 0, status: status(5, 0) },
    ];
    let calls = 0;
    await page.route('**/admin/catalogo/dados/sincronizar', (route) => {
        calls++;
        route.fulfill({ json: replies.shift() });
    });

    await page.getByRole('button', { name: 'Iniciar' }).click();
    await expect(page.locator('[data-count="synced"]')).toHaveText('3');
    // Plain text only: a title from the API is never interpreted as HTML.
    await expect(page.locator('[data-sync-log] li').first()).toHaveText('✓ Primeiro <b>anime</b>');
    await expect(page.locator('[data-count="synced"]')).toHaveText('5', { timeout: 8000 });
    await expect(page.locator('[data-sync-feedback]')).toHaveText('Pronto: não há mais animes na fila.');
    await expect(page.locator('[data-sync-toggle]')).toBeDisabled();
    expect(calls).toBe(4);
});

test('pausing stops requests and an expired session is explained', async ({ page }) => {
    await login(page, 'admin@example.test');
    await page.goto('/admin/catalogo/dados');
    let calls = 0;
    await page.route('**/admin/catalogo/dados/sincronizar', (route) => {
        calls++;
        if (calls === 1) return route.fulfill({ json: { state: 'synced', retry_after: 1, anime: 'A', status: { total: 5, synced: 1, pending: 4, failed: 0 } } });

        return route.fulfill({ status: 419, json: {} });
    });
    await page.getByRole('button', { name: 'Iniciar' }).click();
    await expect(page.locator('[data-sync-log] li')).toHaveCount(1);
    await page.getByRole('button', { name: 'Pausar' }).click();
    await expect(page.getByRole('button', { name: 'Continuar' })).toBeVisible();
    await page.waitForTimeout(1500);
    expect(calls).toBe(1);
    await page.getByRole('button', { name: 'Continuar' }).click();
    await expect(page.locator('[data-sync-feedback]')).toContainText('Sua sessão expirou');
});

test('studio, source and season narrow the catalog and stay selected', async ({ page }) => {
    await login(page);
    await page.goto('/animes');
    await page.getByLabel('Estúdio').selectOption('Wit Studio');
    await page.getByLabel('Origem').selectOption({ label: 'Mangá' });
    await page.getByLabel('Temporada').selectOption({ label: 'Primavera de 2013' });
    await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
    await expect(page.locator('.anime-grid .card-title')).toHaveText(['Shingeki no Kyojin']);
    await expect(page.getByLabel('Estúdio')).toHaveValue('Wit Studio');
    await expect(page.getByLabel('Temporada')).toHaveValue('spring-2013');
});
