// The browser drives the run: each request completes one anime and the server says when to send the next.
const panel = document.querySelector('[data-metadata-sync]');

if (panel) {
    const toggle = panel.querySelector('[data-sync-toggle]');
    const label = panel.querySelector('[data-sync-label]');
    const feedback = panel.querySelector('[data-sync-feedback]');
    const log = panel.querySelector('[data-sync-log]');
    const MAX_UNAVAILABLE = 3;
    let running = false;
    let timer = null;
    let unavailable = 0;

    const stop = (message, text = 'Continuar') => {
        running = false;
        clearTimeout(timer);
        label.textContent = text;
        if (message) feedback.textContent = message;
    };

    const render = (status) => {
        for (const [key, value] of Object.entries(status)) {
            const count = panel.querySelector(`[data-count="${key}"]`);
            if (count) count.textContent = value;
        }
        const progress = panel.querySelector('[data-progress]');
        progress.setAttribute('aria-valuemax', status.total);
        progress.setAttribute('aria-valuenow', status.synced);
        panel.querySelector('[data-progress-fill]').style.width = `${status.total ? (status.synced / status.total) * 100 : 0}%`;
    };

    const addLog = (text) => {
        const item = document.createElement('li');
        item.textContent = text;
        log.prepend(item);
        while (log.children.length > 6) log.lastElementChild.remove();
    };

    const handle = (result) => {
        render(result.status);
        unavailable = result.state === 'unavailable' ? unavailable + 1 : 0;
        switch (result.state) {
            case 'done':
                stop('Pronto: não há mais animes na fila.', 'Iniciar');
                toggle.disabled = true;
                return;
            case 'synced':
                addLog(`✓ ${result.anime}`);
                feedback.textContent = 'Completando dados…';
                break;
            case 'failed':
                addLog(`✕ ${result.anime}: não foi possível completar`);
                break;
            case 'paused':
                feedback.textContent = `A API pediu uma pausa. Continuando em ${result.retry_after} segundos…`;
                break;
            case 'unavailable':
                if (unavailable >= MAX_UNAVAILABLE) {
                    stop('A API parece indisponível. Nada foi perdido; tente continuar mais tarde.');
                    return;
                }
                feedback.textContent = `A API não respondeu. Nova tentativa em ${result.retry_after} segundos…`;
                break;
        }
        timer = setTimeout(step, result.retry_after * 1000);
    };

    async function step() {
        if (!running) return;
        try {
            const response = await fetch(panel.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': panel.dataset.token },
            });
            if (response.status === 401 || response.status === 419) {
                stop('Sua sessão expirou. Atualize a página para continuar.');
                return;
            }
            if (!response.ok) throw new Error(String(response.status));
            const result = await response.json();
            if (running) handle(result);
        } catch {
            stop('Não foi possível falar com o servidor. Nada foi perdido; clique para continuar.');
        }
    }

    toggle.addEventListener('click', () => {
        if (running) {
            stop('Pausado. Clique em continuar quando quiser.');
            return;
        }
        running = true;
        unavailable = 0;
        label.textContent = 'Pausar';
        feedback.textContent = 'Completando dados…';
        step();
    });
}
