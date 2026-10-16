import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Keep Blade as the source of truth, replacing collection fragments after a mutation.
let collectionPending = false;
document.addEventListener('submit', async (event) => {
    const form = event.target;
    const section = form.closest('[data-async-collection]');
    if (!section || !form.matches('[data-collection-action]')) return;
    event.preventDefault();
    if (collectionPending) return;

    collectionPending = true;
    const feedback = document.querySelector('[data-async-feedback]');
    const focusLabel = event.submitter?.getAttribute('aria-label');
    const scrollY = window.scrollY;
    section.setAttribute('aria-busy', 'true');
    if (feedback) feedback.textContent = 'Salvando…';
    try {
        const response = await fetch(form.action, {
            method: 'POST', body: new FormData(form), credentials: 'same-origin',
            headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) throw new Error('Não foi possível salvar. Tente novamente.');
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        if (!page.querySelector('[data-async-summary="stats"]')) {
            throw new Error('Sua sessão pode ter expirado. Atualize a página para entrar novamente.');
        }
        const fresh = [...page.querySelectorAll('[data-async-collection]')];
        for (const current of document.querySelectorAll('[data-async-collection]')) {
            const replacement = fresh.find((item) => item.id === current.id);
            if (replacement && current.innerHTML !== replacement.innerHTML) current.replaceWith(replacement);
            else if (replacement) current.removeAttribute('aria-busy');
            else current.remove();
        }
        // Adding the first Top 10 item can create a section that did not exist before.
        for (const item of fresh) {
            if (!document.getElementById(item.id)) {
                document.querySelector('[data-async-collection]')?.before(item);
            }
        }
        for (const current of document.querySelectorAll('[data-async-summary]')) {
            const replacement = page.querySelector(`[data-async-summary="${current.dataset.asyncSummary}"]`);
            if (replacement && current.innerHTML !== replacement.innerHTML) current.replaceWith(replacement);
        }
        if (feedback) feedback.textContent = 'Coleção atualizada.';
        if (focusLabel) [...document.querySelectorAll('button[aria-label]')]
            .find((button) => button.getAttribute('aria-label') === focusLabel)?.focus({ preventScroll: true });
        window.scrollTo({ top: scrollY, behavior: 'instant' });
    } catch (error) {
        if (feedback) feedback.textContent = error.message;
    } finally {
        section.removeAttribute('aria-busy');
        collectionPending = false;
    }
});
