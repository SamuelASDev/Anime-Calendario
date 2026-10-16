import { test, expect } from '@playwright/test';

test.beforeEach(async ({ page }) => {
    await page.goto('/login');
    await page.getByRole('textbox', { name: 'E-mail', exact: true }).fill('browser@example.test');
    await page.getByRole('textbox', { name: 'Senha', exact: true }).fill('BrowserTest@123');
    await page.getByRole('button', { name: 'Entrar', exact: true }).click();
    await expect(page).toHaveURL(/dashboard/);
    await page.goto('/u/browser_test');
});

test('orders only favorites, fetches only that fragment and survives reload', async ({ page }) => {
    const fullPageBytes = Buffer.byteLength(await page.content());
    const top = await page.locator('#top10 .card-title').allTextContents();
    const completed = await page.locator('#completed .card-title').allTextContents();
    let navigations = 0;
    page.on('framenavigated', (frame) => { if (frame === page.mainFrame()) navigations++; });
    const fragment = page.waitForResponse((response) => response.url().includes('blocks%5B%5D=favorites'));
    await page.getByRole('button', { name: 'Descer Anime 01 em Favoritos', exact: true }).click();
    const payload = await (await fragment).json();
    expect(Object.keys(payload.fragments)).toEqual(['favorites']);
    expect(Buffer.byteLength(JSON.stringify(payload))).toBeLessThan(fullPageBytes);
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 02');
    expect(await page.locator('#top10 .card-title').allTextContents()).toEqual(top);
    expect(await page.locator('#completed .card-title').allTextContents()).toEqual(completed);
    expect(navigations).toBe(0);
    await page.reload();
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 02');
    await page.getByRole('button', { name: 'Subir Anime 01 em Favoritos', exact: true }).click();
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 01');
});

test('top ten can become empty and be created again without reloading', async ({ page }) => {
    for (const title of ['Anime 01', 'Anime 02', 'Anime 03']) {
        await page.getByRole('button', { name: `Remover ${title} do Top 10`, exact: true }).click();
        await expect(page.locator('#top10 .card-title').filter({ hasText: title })).toHaveCount(0);
    }
    await expect(page.locator('#top10')).toBeHidden();
    const card = (title) => page.locator('#favorites article').filter({ has: page.getByRole('link', { name: title, exact: true }) });
    for (const title of ['Anime 03', 'Anime 02', 'Anime 01']) {
        await card(title).getByRole('button', { name: 'Top 10', exact: true }).click();
        await expect(page.locator('#top10 .card-title').first()).toHaveText(title);
    }
    await expect(page.locator('[data-async-summary="nav"]').getByRole('link', { name: 'Top 10', exact: true })).toBeVisible();
});

test('favorite toggle synchronizes count and other blocks without navigation', async ({ page }) => {
    await page.locator('#favorites article').filter({ has: page.getByRole('link', { name: 'Anime 01', exact: true }) })
        .getByRole('button', { name: 'Favorito', exact: true }).click();
    await expect(page.locator('#favorites .card-title').filter({ hasText: 'Anime 01' })).toHaveCount(0);
    await expect(page.locator('[data-async-summary="stats"]')).toContainText('13');
    const topCard = page.locator('#top10 article').filter({ has: page.getByRole('link', { name: 'Anime 01', exact: true }) });
    await topCard.getByRole('button', { name: 'Favoritar', exact: true }).click();
    await expect(page.locator('[data-async-summary="stats"]')).toContainText('14');
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 01');
});

test('mobile menu, filters and details remain usable', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.getByRole('button', { name: 'Abrir menu', exact: true }).click();
    await expect(page.getByRole('navigation', { name: 'Navegação móvel' })).toBeVisible();
    await page.getByRole('link', { name: 'Explorar animes', exact: true }).click();
    await page.getByRole('searchbox', { name: 'Buscar pelo título', exact: true }).fill('Anime 01');
    await page.getByRole('button', { name: 'Aplicar filtros', exact: true }).click();
    await expect(page.locator('article')).toHaveCount(1);
    await page.getByRole('link', { name: 'Anime 01', exact: true }).click();
    await expect(page.getByRole('heading', { name: 'Anime 01', exact: true })).toBeVisible();
    const overflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
    expect(overflow).toBe(false);
});

test('session expiry keeps the page intact and explains the failure', async ({ page }) => {
    await page.route('**/collection-order', (route) => route.fulfill({ status: 419, contentType: 'application/json', body: '{}' }));
    await page.getByRole('button', { name: 'Descer Anime 01 em Favoritos', exact: true }).click();
    await expect(page.locator('[data-async-feedback]')).toContainText('Sua sessão expirou');
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 01');
    await expect(page.locator('#favorites')).not.toHaveAttribute('aria-busy', 'true');
});

test('completed order changes only completed and persists', async ({ page }) => {
    const favorites = await page.locator('#favorites .card-title').allTextContents();
    const top = await page.locator('#top10 .card-title').allTextContents();
    await page.getByRole('button', { name: 'Descer Anime 14 em Concluídos', exact: true }).click();
    await expect(page.locator('#completed .card-title').first()).toHaveText('Anime 13');
    expect(await page.locator('#favorites .card-title').allTextContents()).toEqual(favorites);
    expect(await page.locator('#top10 .card-title').allTextContents()).toEqual(top);
    await page.reload();
    await expect(page.locator('#completed .card-title').first()).toHaveText('Anime 13');
    await page.getByRole('button', { name: 'Subir Anime 14 em Concluídos', exact: true }).click();
    await expect(page.locator('#completed .card-title').first()).toHaveText('Anime 14');
});

test('fragment failure after saving explains that the database was updated', async ({ page }) => {
    await page.route('**/u/browser_test?**', (route) => route.fulfill({ status: 503, body: '' }));
    await page.getByRole('button', { name: 'Descer Anime 01 em Favoritos', exact: true }).click();
    await expect(page.locator('[data-async-feedback]')).toContainText('Alteração salva');
    await page.unroute('**/u/browser_test?**');
    await page.reload();
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 02');
    await page.getByRole('button', { name: 'Subir Anime 01 em Favoritos', exact: true }).click();
    await expect(page.locator('#favorites .card-title').first()).toHaveText('Anime 01');
});
