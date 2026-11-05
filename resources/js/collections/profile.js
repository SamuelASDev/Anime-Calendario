// Mutations return affected block names; reads return only those Blade fragments.
const ALL_BLOCKS = ['top10', 'favorites', 'watching', 'completed', 'planned', 'reviews', 'stats', 'nav'];
const queue = [];
let draining = false;
let activeAction = null;

document.addEventListener('submit', (event) => {
    const form = event.target;
    const section = form.closest('[data-async-collection]');
    if (event.defaultPrevented || !section || !form.matches('[data-collection-action]')) return;
    event.preventDefault();

    // Repeating an order arrow is intentional and queues; repeating a toggle while it saves would undo it.
    const action = form.action;
    if (!form.hasAttribute('data-repeatable') && queue.some((job) => job.action === action)) return;
    if (!form.hasAttribute('data-repeatable') && activeAction === action) return;

    section.setAttribute('aria-busy', 'true');
    queue.push({ action, body: new FormData(form), section, focusLabel: event.submitter?.getAttribute('aria-label') });
    drain();
});

async function drain() {
    if (draining) return;
    draining = true;
    const feedback = document.querySelector('[data-async-feedback]');
    const scrollY = window.scrollY;
    const blocks = new Set();
    const sections = new Set();
    let message = null;
    let focusLabel = null;
    let failure = null;
    if (feedback) feedback.textContent = 'Salvando…';

    // Clicks made while saving join this run, so a burst of changes refreshes the page only once.
    while (queue.length && !failure) {
        const job = queue.shift();
        activeAction = job.action;
        sections.add(job.section);
        focusLabel = job.focusLabel ?? focusLabel;
        try {
            const mutation = await request(job.action, { method: 'POST', body: job.body });
            mutation.blocks.forEach((block) => blocks.add(block));
            message = mutation.message;
        } catch (error) {
            failure = error;
            queue.forEach((pending) => sections.add(pending.section));
            queue.length = 0;
        }
    }
    activeAction = null;

    try {
        if (blocks.size) await syncBlocks([...blocks]);
        if (feedback) feedback.textContent = failure ? failure.message : message;
        if (focusLabel) [...document.querySelectorAll('button[aria-label]')]
            .find((button) => button.getAttribute('aria-label') === focusLabel)?.focus({ preventScroll: true });
        window.scrollTo({ top: scrollY, behavior: 'instant' });
    } catch {
        if (feedback) feedback.textContent = 'Alteração salva, mas a exibição não foi atualizada. Recarregue a página para conferir.';
    } finally {
        sections.forEach((section) => section.removeAttribute('aria-busy'));
        draining = false;
        if (queue.length) drain();
    }
}

async function syncBlocks(blocks) {
    const url = new URL(window.location.href);
    url.searchParams.delete('blocks[]');
    url.searchParams.delete('blocks');
    blocks.forEach((block) => url.searchParams.append('blocks[]', block));
    const { fragments } = await request(url);
    for (const [block, html] of Object.entries(fragments)) {
        const current = document.getElementById(block) || document.querySelector('[data-async-summary="' + block + '"]');
        if (!current) continue;
        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const replacement = template.content.firstElementChild;
        if (replacement) current.replaceWith(replacement);
    }
}

// Returning through browser history can restore an old copy of the page; bring the collections up to date.
window.addEventListener('pageshow', (event) => {
    if (!document.querySelector('[data-async-collection]')) return;
    const type = performance.getEntriesByType('navigation')[0]?.type;
    if (event.persisted || type === 'back_forward') syncBlocks(ALL_BLOCKS).catch(() => {});
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
