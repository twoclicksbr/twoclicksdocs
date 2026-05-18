<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TwoClicks Docs — Painel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'tc-dark': '#0a0a0a',
                        'tc-card': '#171717',
                        'tc-border': '#262626',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-tc-dark text-gray-100 min-h-screen">

<!-- Modal de tokens (primeira visita) -->
<div id="tokenModal" class="hidden fixed inset-0 bg-black bg-opacity-80 z-50 flex items-center justify-center p-4">
    <div class="bg-tc-card border border-tc-border rounded-lg p-6 w-full max-w-2xl">
        <h2 class="text-xl font-bold mb-2">Configure seus tokens</h2>
        <p class="text-sm text-gray-400 mb-4">Cole o token Sanctum do ator <span class="text-yellow-400">alex</span> de cada projeto. Os tokens ficam salvos no seu navegador (localStorage).</p>
        <div class="space-y-3">
            <input type="text" id="tk-smartclick360" placeholder="Token do SmartClick360" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm font-mono">
            <input type="text" id="tk-bethel360" placeholder="Token do Bethel360" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm font-mono">
            <input type="text" id="tk-apdireta" placeholder="Token do ApDireta" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm font-mono">
            <input type="text" id="tk-clickbank" placeholder="Token do ClickBank" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm font-mono">
            <input type="text" id="tk-whatspanel" placeholder="Token do WhatsPanel" class="w-full bg-tc-dark border border-tc-border rounded px-3 py-2 text-sm font-mono">
        </div>
        <div class="flex justify-end gap-2 mt-4">
            <button onclick="saveTokens()" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">Salvar</button>
        </div>
        <p class="text-xs text-gray-500 mt-3">Você pode atualizar depois pelo botão no canto superior direito. Tokens NÃO são enviados ao servidor — só ficam no navegador.</p>
    </div>
</div>

<!-- Layout principal -->
<header class="border-b border-tc-border px-6 py-3 flex items-center justify-between">
    <div class="flex items-center gap-4">
        <h1 class="text-lg font-bold">TwoClicks Docs</h1>
        <select id="projectSelector" onchange="onProjectChange()" class="bg-tc-card border border-tc-border rounded px-3 py-1 text-sm">
            <option value="smartclick360">SmartClick360</option>
            <option value="bethel360">Bethel360</option>
            <option value="apdireta">ApDireta</option>
            <option value="clickbank">ClickBank</option>
            <option value="whatspanel">WhatsPanel</option>
        </select>
        <div class="flex gap-2 ml-4">
            <button id="tabDocs" onclick="switchTab('documentacao')" class="px-4 py-1.5 text-sm rounded">Documentação</button>
            <button id="tabTasks" onclick="switchTab('tarefas')" class="px-4 py-1.5 text-sm rounded">Tarefas</button>
        </div>
    </div>
    <div class="flex items-center gap-3">
        <button onclick="openTokenModal()" class="text-xs text-gray-400 hover:text-white">Atualizar tokens</button>
        <button onclick="createShare()" class="text-xs text-gray-400 hover:text-white">🔗 Compartilhar visualização</button>
    </div>
</header>

<main class="grid grid-cols-12 gap-4 p-4">

    <!-- Coluna esquerda: árvore de documentos -->
    <aside id="paneDocs" class="col-span-3 bg-tc-card border border-tc-border rounded-lg p-4 max-h-[calc(100vh-120px)] overflow-y-auto">
        <h2 class="text-sm font-bold uppercase text-gray-400 mb-3">Documentos</h2>
        <div id="docsTree" class="text-sm space-y-1">
            <div class="text-gray-500">Carregando...</div>
        </div>
    </aside>

    <!-- Coluna central: tarefas -->
    <section id="paneTasks" class="col-span-5 bg-tc-card border border-tc-border rounded-lg p-4 max-h-[calc(100vh-120px)] overflow-y-auto">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-sm font-bold uppercase text-gray-400">Tarefas</h2>
            <select id="statusFilter" onchange="loadTasks()" class="bg-tc-dark border border-tc-border rounded px-2 py-1 text-xs">
                <option value="">Todos status</option>
            </select>
        </div>
        <div id="tasksList" class="space-y-2">
            <div class="text-gray-500 text-sm">Carregando...</div>
        </div>
    </section>

    <!-- Coluna direita: detalhes -->
    <section id="paneDetail" class="col-span-4 bg-tc-card border border-tc-border rounded-lg p-4 max-h-[calc(100vh-120px)] overflow-y-auto">
        <h2 class="text-sm font-bold uppercase text-gray-400 mb-3">Detalhes</h2>
        <div id="detailPane" class="text-sm text-gray-400">
            Selecione um documento ou tarefa.
        </div>
    </section>

</main>

<script>
const API_BASE = 'https://docs.twoclicks.com.br/api';
const PROJECTS = ['smartclick360', 'bethel360', 'apdireta', 'clickbank', 'whatspanel'];

// ===== Token management =====
function getToken(project) {
    return localStorage.getItem(`tcdoc_token_${project}`);
}

function saveTokens() {
    PROJECTS.forEach(p => {
        const v = document.getElementById(`tk-${p}`).value.trim();
        if (v) localStorage.setItem(`tcdoc_token_${p}`, v);
    });
    document.getElementById('tokenModal').classList.add('hidden');
    init();
}

function openTokenModal() {
    PROJECTS.forEach(p => {
        document.getElementById(`tk-${p}`).value = getToken(p) || '';
    });
    document.getElementById('tokenModal').classList.remove('hidden');
}

function hasAnyToken() {
    return PROJECTS.some(p => getToken(p));
}

// ===== API client =====
async function api(project, path) {
    const token = getToken(project);
    if (!token) throw new Error(`Sem token para ${project}`);
    const res = await fetch(`${API_BASE}${path}`, {
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`,
        }
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
}

// ===== State =====
let currentProject = 'smartclick360';
let statusesCache = [];
let currentFilters = {};
let currentView = null; // { type: 'doc'|'task', id }

// ===== Loaders =====
async function loadStatuses() {
    try {
        const data = await api(currentProject, '/doc/task-statuses');
        statusesCache = data.data || [];
        const sel = document.getElementById('statusFilter');
        sel.innerHTML = '<option value="">Todos status</option>' +
            statusesCache.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    } catch (e) {
        console.error('Erro statuses:', e);
    }
}

async function loadDocs() {
    const tree = document.getElementById('docsTree');
    tree.innerHTML = '<div class="text-gray-500">Carregando...</div>';
    try {
        const data = await api(currentProject, '/doc/documents?per_page=200');
        const docs = data.data || [];
        renderDocsTree(docs);
    } catch (e) {
        tree.innerHTML = `<div class="text-red-400 text-xs">Erro: ${e.message}</div>`;
    }
}

function renderDocsTree(docs) {
    const tree = document.getElementById('docsTree');
    const byId = Object.fromEntries(docs.map(d => [d.id, {...d, children: []}]));
    const roots = [];
    docs.forEach(d => {
        if (d.parent_id && byId[d.parent_id]) {
            byId[d.parent_id].children.push(byId[d.id]);
        } else {
            roots.push(byId[d.id]);
        }
    });
    tree.innerHTML = roots.map(r => renderDocNode(r, 0)).join('');
}

function renderDocNode(doc, depth) {
    const pad = depth * 12;
    const children = doc.children.map(c => renderDocNode(c, depth + 1)).join('');
    return `
        <div>
            <button onclick="showDoc(${doc.id})" style="padding-left:${pad}px" class="block w-full text-left hover:bg-tc-dark px-2 py-1 rounded text-gray-300 hover:text-white truncate">
                ${depth > 0 ? '└ ' : ''}${escapeHtml(doc.title)}
            </button>
            ${children}
        </div>
    `;
}

async function loadTasks() {
    const list = document.getElementById('tasksList');
    list.innerHTML = '<div class="text-gray-500 text-sm">Carregando...</div>';
    const statusFilter = document.getElementById('statusFilter').value;

    currentFilters = {};
    if (statusFilter) currentFilters.task_status_id = parseInt(statusFilter, 10);

    try {
        const params = new URLSearchParams({ per_page: '100', expand: 'status,fase,modulo,tipo,prioridade' });
        if (statusFilter) params.set('task_status_id', statusFilter);
        const data = await api(currentProject, `/doc/tasks?${params}`);
        const tasks = data.data || [];
        if (tasks.length === 0) {
            list.innerHTML = '<div class="text-gray-500 text-sm">Nenhuma tarefa.</div>';
            return;
        }
        list.innerHTML = tasks.map(renderTaskCard).join('');
    } catch (e) {
        list.innerHTML = `<div class="text-red-400 text-xs">Erro: ${e.message}</div>`;
    }
}

function renderTaskCard(t) {
    const statusName = t.task_status?.name || t.status?.name || 'Sem status';
    const faseName = t.fase?.name || '—';
    const tipoName = t.tipo?.name || '—';
    const prioName = t.prioridade?.name || '—';
    const prioColor = t.prioridade?.color || '#666';
    return `
        <div onclick="showTask(${t.id})" class="bg-tc-dark border border-tc-border rounded p-3 cursor-pointer hover:border-blue-500 transition">
            <div class="flex items-start justify-between gap-2">
                <div class="font-medium text-sm">${escapeHtml(t.title)}</div>
                <span class="text-xs px-2 py-0.5 rounded" style="background:${prioColor}33;color:${prioColor}">${prioName}</span>
            </div>
            <div class="flex flex-wrap gap-2 mt-2 text-xs text-gray-400">
                <span class="bg-tc-border px-2 py-0.5 rounded">${statusName}</span>
                <span>${faseName}</span>
                <span>· ${tipoName}</span>
            </div>
        </div>
    `;
}

// ===== Detail pane =====
async function showDoc(id) {
    currentView = { type: 'doc', id };
    const pane = document.getElementById('detailPane');
    pane.innerHTML = '<div class="text-gray-500">Carregando...</div>';
    try {
        const doc = (await api(currentProject, `/doc/documents/${id}`)).data;
        const blocks = (await api(currentProject, `/doc/documents/${id}/blocks?per_page=200`)).data || [];
        pane.innerHTML = `
            <h3 class="text-lg font-bold mb-1">${escapeHtml(doc.title)}</h3>
            <div class="text-xs text-gray-500 mb-3">slug: ${doc.slug}</div>
            <div class="space-y-3 text-sm text-gray-200">
                ${blocks.map(b => `<div class="bg-tc-dark border border-tc-border rounded p-3 whitespace-pre-wrap break-words">${escapeHtml(b.content)}</div>`).join('')}
                ${blocks.length === 0 ? '<div class="text-gray-500">Sem blocos.</div>' : ''}
            </div>
        `;
    } catch (e) {
        pane.innerHTML = `<div class="text-red-400 text-xs">Erro: ${e.message}</div>`;
    }
}

async function showTask(id) {
    currentView = { type: 'task', id };
    const pane = document.getElementById('detailPane');
    pane.innerHTML = '<div class="text-gray-500">Carregando...</div>';
    try {
        const task = (await api(currentProject, `/doc/tasks/${id}?expand=status,fase,modulo,tipo,prioridade,details`)).data;
        const details = task.details || [];
        const statusName = task.task_status?.name || task.status?.name || '—';
        pane.innerHTML = `
            <h3 class="text-lg font-bold mb-1">${escapeHtml(task.title)}</h3>
            <div class="text-xs text-gray-500 mb-3">id: ${task.id} · ${statusName}</div>
            ${task.description ? `<div class="bg-tc-dark border border-tc-border rounded p-3 text-sm whitespace-pre-wrap mb-3">${escapeHtml(task.description)}</div>` : ''}
            <h4 class="text-xs font-bold uppercase text-gray-400 mb-2">Ciclos (${details.length})</h4>
            <div class="space-y-2">
                ${details.map(d => `
                    <div class="bg-tc-dark border border-tc-border rounded p-3 text-xs">
                        <div class="flex justify-between text-gray-400 mb-1">
                            <span>${d.started_at || '—'} → ${d.finished_at || 'em aberto'}</span>
                            <span>${d.duration_minutes ?? '—'} min</span>
                        </div>
                        <div class="text-gray-200 whitespace-pre-wrap">${escapeHtml(d.prompt || '')}</div>
                        ${d.resumo ? `<div class="mt-2 pt-2 border-t border-tc-border text-gray-400 whitespace-pre-wrap">${escapeHtml(d.resumo)}</div>` : ''}
                    </div>
                `).join('')}
                ${details.length === 0 ? '<div class="text-gray-500 text-xs">Sem ciclos.</div>' : ''}
            </div>
        `;
    } catch (e) {
        pane.innerHTML = `<div class="text-red-400 text-xs">Erro: ${e.message}</div>`;
    }
}

// ===== Helpers =====
function escapeHtml(s) {
    if (s == null) return '';
    return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function onProjectChange() {
    currentProject = document.getElementById('projectSelector').value;
    document.getElementById('detailPane').innerHTML = '<div class="text-gray-400">Selecione um documento ou tarefa.</div>';
    loadAll();
}

async function loadAll() {
    await loadStatuses();
    await Promise.all([loadDocs(), loadTasks()]);
}

// ===== Shares =====
async function createShare() {
    const tab = localStorage.getItem('tcdoc_active_tab') || 'documentacao';
    const payload = { tab, filters: currentFilters };
    if (currentView) payload.resource = { type: currentView.type, id: currentView.id };

    const token = getToken(currentProject);
    if (!token) { alert('Configure o token deste projeto antes.'); return; }

    try {
        const res = await fetch(`${API_BASE}/doc/shares`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
            },
            body: JSON.stringify({ payload }),
        });
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();
        const url = `${window.location.origin}/painel?h=${data.data.hash}`;
        await navigator.clipboard.writeText(url);
        showToast(`Link copiado: ${url}`);
    } catch (e) {
        alert(`Erro: ${e.message}`);
    }
}

function showToast(msg) {
    const el = document.createElement('div');
    el.className = 'fixed bottom-4 right-4 bg-blue-600 text-white px-4 py-2 rounded shadow-lg text-sm z-50';
    el.textContent = msg;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}

async function resolveShare(hash) {
    let token = null;
    for (const p of PROJECTS) { token = getToken(p); if (token) break; }
    if (!token) {
        alert('Configure pelo menos 1 token para abrir links compartilhados.');
        openTokenModal();
        return;
    }

    try {
        const res = await fetch(`${API_BASE}/shares/${hash}`, {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
        });
        if (!res.ok) throw new Error(`HTTP ${res.status} — link inválido ou expirado`);

        const share = (await res.json()).data;
        const projectSlug = share.project?.slug;

        if (!projectSlug || !PROJECTS.includes(projectSlug)) {
            throw new Error('Projeto do share não está disponível');
        }
        if (!getToken(projectSlug)) {
            alert(`Este link aponta para o projeto "${projectSlug}", mas você não tem token configurado. Configure-o.`);
            openTokenModal();
            return;
        }

        currentProject = projectSlug;
        document.getElementById('projectSelector').value = currentProject;

        const payload = share.payload || {};
        if (payload.tab) localStorage.setItem('tcdoc_active_tab', payload.tab);

        applyTab();
        await loadAll();

        if (payload.filters?.task_status_id) {
            document.getElementById('statusFilter').value = payload.filters.task_status_id;
            await loadTasks();
        }

        if (payload.resource) {
            if (payload.resource.type === 'doc') showDoc(payload.resource.id);
            else if (payload.resource.type === 'task') showTask(payload.resource.id);
        }

        showToast(`Visualização aberta de ${share.project.name}`);
    } catch (e) {
        alert(`Erro ao abrir o link: ${e.message}`);
        applyTab();
        loadAll();
    }
}

function switchTab(tab) {
    localStorage.setItem('tcdoc_active_tab', tab);
    applyTab();
}

function applyTab() {
    const tab = localStorage.getItem('tcdoc_active_tab') || 'documentacao';
    const tabDocsBtn = document.getElementById('tabDocs');
    const tabTasksBtn = document.getElementById('tabTasks');
    const paneDocs = document.getElementById('paneDocs');
    const paneTasks = document.getElementById('paneTasks');
    const paneDetail = document.getElementById('paneDetail');

    const inactive = 'border border-tc-border text-gray-400 hover:text-white hover:border-gray-500';
    const active = 'bg-blue-600 text-white';
    tabDocsBtn.className = 'px-4 py-1.5 text-sm rounded';
    tabTasksBtn.className = 'px-4 py-1.5 text-sm rounded';

    if (tab === 'documentacao') {
        tabDocsBtn.className += ' ' + active;
        tabTasksBtn.className += ' ' + inactive;
        paneDocs.classList.remove('hidden');
        paneTasks.classList.add('hidden');
        paneDocs.className = paneDocs.className.replace(/col-span-\d+/g, '') + ' col-span-4';
        paneDetail.className = paneDetail.className.replace(/col-span-\d+/g, '') + ' col-span-8';
    } else {
        tabDocsBtn.className += ' ' + inactive;
        tabTasksBtn.className += ' ' + active;
        paneDocs.classList.add('hidden');
        paneTasks.classList.remove('hidden');
        paneTasks.className = paneTasks.className.replace(/col-span-\d+/g, '') + ' col-span-5';
        paneDetail.className = paneDetail.className.replace(/col-span-\d+/g, '') + ' col-span-7';
    }
}

async function init() {
    if (!hasAnyToken()) {
        openTokenModal();
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const hashParam = params.get('h');

    if (hashParam) {
        await resolveShare(hashParam);
        return;
    }

    applyTab();
    await loadAll();
}

// ===== Boot =====
init();
</script>

</body>
</html>
