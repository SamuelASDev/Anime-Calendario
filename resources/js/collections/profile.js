// Mutations return affected block names; reads return only those Blade fragments.
let pending = false;

document.addEventListener('submit', async (event) => {
    const form = event.target;
    const section = form.closest('[data-async-collection]');
    if (!section || !form.matches('[data-collection-action]')) return;
    event.preventDefault();
    if (pending) return;

    pending = true;
    const feedback = document.querySelector('[data-async-feedback]');
    const focusLabel = event.submitter?.getAttribute('aria-label');
    const scrollY = window.scrollY;
    section.setAttribute('aria-busy', 'true');
    if (feedback) feedback.textContent = 'Salvando…';
    let saved = false;
    try {
        const mutation = await request(form.action, { method: 'POST', body: new FormData(form) });
        saved = true;
        const url = new URL(window.location.href);
        url.searchParams.delete('blocks[]');
        url.searchParams.delete('blocks');
        mutation.blocks.forEach((block) => url.searchParams.append('blocks[]', block));
        const { fragments } = await request(url);
        for (const [block, html] of Object.entries(fragments)) {
            const current = document.getElementById(block) || document.querySelector('[data-async-summary="' + block + '"]');
            if (!current) continue;
            const template = document.createElement('template');
            template.innerHTML = html.trim();
            const replacement = template.content.firstElementChild;
            if (replacement) current.replaceWith(replacement);
        }
        if (feedback) feedback.textContent = mutation.message;
        if (focusLabel) [...document.querySelectorAll('button[aria-label]')]
            .find((button) => button.getAttribute('aria-label') === focusLabel)?.focus({ preventScroll: true });
        window.scrollTo({ top: scrollY, behavior: 'instant' });
    } catch (error) {
        if (feedback) feedback.textContent = saved
            ? 'Alteração salva, mas a exibição não foi atualizada. Recarregue a página para conferir.'
            : error.message;
    } finally {
        section.removeAttribute('aria-busy');
        pending = false;
    }
});

async function request(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin', ...options,
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) {
        const message = response.status === 401 || response.status === 419
            ? 'Sua sessão expirou. Atualize a página para entrar novamente.'
            : 'Não foi possível atualizar a coleção. Tente novamente.';
        throw new Error(message);
    }
    return response.json();
}
