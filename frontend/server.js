const express  = require('express');
const fetch    = require('node-fetch');
const multer   = require('multer');
const FormData = require('form-data');

const app    = express();
const PORT   = process.env.PORT || 3001;
const API_URL = process.env.API_URL || 'http://nginx:80/api';
const upload = multer({ storage: multer.memoryStorage() });

app.use(express.urlencoded({ extended: true }));
app.use(express.json());

function escHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function renderErrors(errors) {
    if (!errors || !Object.keys(errors).length) return '';
    const items = Object.values(errors).flat().map(e => `<li>${escHtml(e)}</li>`).join('');
    return `<ul style="color:red">${items}</ul>`;
}

function fieldError(errors, field) {
    if (!errors?.[field]) return '';
    return `<span style="color:red">${escHtml(errors[field][0])}</span>`;
}

function layout(title, metaDesc, body) {
    return `<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="${escHtml(metaDesc)}">
    <title>${escHtml(title)}</title>
</head>
<body>
${body}
</body>
</html>`;
}

function paginationHtml(meta, perPage, filters) {
    if (!meta) return '';
    const page        = parseInt(meta.page, 10);
    const total_pages = parseInt(meta.total_pages, 10);
    const f           = filters ? `&filters=${encodeURIComponent(filters)}` : '';

    let html = `<form method="GET" action="/" style="display:inline">
        <input type="hidden" name="page" value="1">
        ${filters ? `<input type="hidden" name="filters" value="${escHtml(filters)}">` : ''}
        Na stránce:
        <select name="per_page" onchange="this.form.submit()">
            ${[5, 10, 25, 50].map(n => `<option value="${n}"${n === parseInt(perPage, 10) ? ' selected' : ''}>${n}</option>`).join('')}
        </select>
    </form> &nbsp; `;

    if (total_pages <= 1) return html + `<strong>[1]</strong>`;

    if (page > 1) html += `<a href="/?page=${page - 1}&per_page=${perPage}${f}">« Předchozí</a> `;

    const show = new Set([1, total_pages]);
    for (let i = Math.max(1, page - 2); i <= Math.min(total_pages, page + 2); i++) show.add(i);
    const sorted = [...show].sort((a, b) => a - b);

    let prev = 0;
    for (const p of sorted) {
        if (prev && p - prev > 1) html += `<span>...</span> `;
        html += p === page
            ? `<strong>[${p}]</strong> `
            : `<a href="/?page=${p}&per_page=${perPage}${f}">${p}</a> `;
        prev = p;
    }

    if (page < total_pages) html += `<a href="/?page=${page + 1}&per_page=${perPage}${f}">Následující »</a>`;

    return html;
}

app.get('/', async (req, res) => {
    const page    = parseInt(req.query.page     || '1',  10);
    const perPage = parseInt(req.query.per_page || '10', 10);
    const filters = req.query.filters || '';

    let apiUrl = `${API_URL}/contacts/?paginate=true&page=${page}&per_page=${perPage}`;
    if (filters) apiUrl += `&filters=${encodeURIComponent(filters)}`;

    let data     = { items: [], _meta: null };
    let apiError = null;

    try {
        const apiRes = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
        data         = await apiRes.json();
    } catch (e) {
        apiError = e.message;
    }

    const contacts = data.items ?? [];

    let parsedFilters = { first_name: '', last_name: '', email: '' };
    if (filters) {
        try {
            const decoded = JSON.parse(Buffer.from(filters, 'base64').toString('utf8'));
            decoded.forEach(f => { parsedFilters[f.f] = f.v; });
        } catch (e) {}
    }

    const rows = contacts.map(c => `
        <tr>
            <td>${escHtml(c.first_name)}</td>
            <td>${escHtml(c.last_name)}</td>
            <td><a href="mailto:${escHtml(c.email)}">${escHtml(c.email)}</a></td>
            <td>
                <a href="/${c.id}">Upravit</a>
                &nbsp;
                <form method="POST" action="/smazat/${c.id}" style="display:inline" onsubmit="return confirm('Opravdu smazat?')">
                    <button type="submit">Smazat</button>
                </form>
            </td>
        </tr>`).join('');

    const body = `
<h1>Seznam kontaktů</h1>
${apiError ? `<p style="color:red">Chyba při načítání kontaktů: ${escHtml(apiError)}</p>` : ''}
<p><a href="/novy-kontakt">+ Přidat nový kontakt</a></p>

<hr>

<h2>Import XML</h2>
<input type="file" id="import-file" accept=".xml,text/xml,application/xml">
<button id="import-btn" disabled>Spustit import</button>

<div id="import-status" style="display:none; margin-top:10px;">
    <p id="import-status-msg"></p>

    <div id="import-stats" style="display:none;">
        <table border="1" cellpadding="4" cellspacing="0">
            <tr>
                <th>Celkem</th><th>Importováno</th><th>Duplikáty</th><th>Neplatné</th><th>Čas</th>
            </tr>
            <tr>
                <td id="stat-total">-</td>
                <td id="stat-imported">-</td>
                <td id="stat-duplicates">-</td>
                <td id="stat-invalid">-</td>
                <td id="stat-duration">-</td>
            </tr>
        </table>
    </div>

    <div id="failures-section" style="display:none; margin-top:10px;">
        <h3>Problematické záznamy</h3>
        <table border="1" cellpadding="4" cellspacing="0">
            <thead>
                <tr><th>Důvod</th><th>Jméno</th><th>Příjmení</th><th>E-mail</th></tr>
            </thead>
            <tbody id="failures-tbody"></tbody>
        </table>
    </div>
</div>

<hr>

<div id="contacts-table-wrap">
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr><th>Jméno</th><th>Příjmení</th><th>E-mail</th><th>Akce</th></tr>
        <tr>
            <th><input type="text" id="filter-first_name" placeholder="Filtrovat..." value="${escHtml(parsedFilters.first_name)}"></th>
            <th><input type="text" id="filter-last_name" placeholder="Filtrovat..." value="${escHtml(parsedFilters.last_name)}"></th>
            <th><input type="text" id="filter-email" placeholder="Filtrovat..." value="${escHtml(parsedFilters.email)}"></th>
            <th><button id="filter-apply">Filtrovat</button></th>
        </tr>
    </thead>
    <tbody>
        ${rows || '<tr><td colspan="4">Žádné kontakty nenalezeny.</td></tr>'}
    </tbody>
</table>

<br>
<div>${paginationHtml(data._meta, perPage, filters)}</div>
</div>

<script>
    const fileInput   = document.getElementById('import-file');
    const importBtn   = document.getElementById('import-btn');
    const statusDiv   = document.getElementById('import-status');
    const statusMsg   = document.getElementById('import-status-msg');
    const statsDiv    = document.getElementById('import-stats');
    const failSection = document.getElementById('failures-section');
    const failTbody   = document.getElementById('failures-tbody');

    function buildFilters() {
        const filters = [];
        [['first_name', 'filter-first_name'], ['last_name', 'filter-last_name'], ['email', 'filter-email']].forEach(([field, id]) => {
            const val = document.getElementById(id).value.trim();
            if (val) filters.push({ f: field, o: 'contains', v: val });
        });
        return filters;
    }

    function applyFilters() {
        const filters = buildFilters();
        const params  = new URLSearchParams(window.location.search);
        params.set('page', '1');
        if (filters.length) {
            params.set('filters', btoa(JSON.stringify(filters)));
        } else {
            params.delete('filters');
        }
        window.location.href = '/?' + params.toString();
    }

    document.addEventListener('click', e => {
        if (e.target && e.target.id === 'filter-apply') applyFilters();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Enter' && ['filter-first_name', 'filter-last_name', 'filter-email'].includes(e.target.id)) applyFilters();
    });

    fileInput.addEventListener('change', () => {
        importBtn.disabled = !fileInput.files.length;
    });

    importBtn.addEventListener('click', async () => {
        const file = fileInput.files[0];
        if (!file) return;

        importBtn.disabled        = true;
        statusDiv.style.display   = 'block';
        statsDiv.style.display    = 'none';
        failSection.style.display = 'none';
        failTbody.innerHTML       = '';
        statusMsg.textContent     = 'Nahrávám soubor...';

        const formData = new FormData();
        formData.append('file', file);

        let importId;
        try {
            const res  = await fetch('/import', { method: 'POST', body: formData });
            const data = await res.json();
            importId   = data.import_id;
            statusMsg.textContent = 'Import probíhá...';
        } catch (err) {
            statusMsg.textContent = 'Chyba: ' + err.message;
            importBtn.disabled = false;
            return;
        }

        const poll = setInterval(async () => {
            try {
                const res  = await fetch('/import/' + importId);
                const data = await res.json();

                if (data.status === 'pending' || data.status === 'processing') return;

                clearInterval(poll);

                if (data.status === 'failed') {
                    statusMsg.textContent = 'Import selhal.';
                    importBtn.disabled = false;
                    return;
                }

                statusMsg.textContent  = 'Import dokončen.';
                statsDiv.style.display = 'block';
                document.getElementById('stat-total').textContent      = data.total;
                document.getElementById('stat-imported').textContent   = data.imported;
                document.getElementById('stat-duplicates').textContent = data.duplicates;
                document.getElementById('stat-invalid').textContent    = data.invalid;
                document.getElementById('stat-duration').textContent   = data.duration + 's';

                if (data.duplicates > 0 || data.invalid > 0) {
                    const fRes = await fetch('/import/' + importId + '/failures');
                    if (fRes.ok) {
                        const fData = await fRes.json();
                        const rows  = [
                            ...(fData.duplicates ?? []).map(f => ({ ...f, reason: 'Duplikát' })),
                            ...(fData.invalid    ?? []).map(f => ({ ...f, reason: 'Neplatné' })),
                        ];
                        if (rows.length) {
                            failSection.style.display = 'block';
                            failTbody.innerHTML = rows.map(f =>
                                '<tr>' +
                                '<td>' + escHtml(f.reason) + '</td>' +
                                '<td>' + escHtml(f.firstName ?? f.first_name ?? '') + '</td>' +
                                '<td>' + escHtml(f.lastName  ?? f.last_name  ?? '') + '</td>' +
                                '<td>' + escHtml(f.email ?? '') + '</td>' +
                                '</tr>'
                            ).join('');
                        }
                    }
                }

                importBtn.disabled = false;
                refreshContacts();
            } catch (err) {
                clearInterval(poll);
                statusMsg.textContent = 'Chyba při kontrole stavu: ' + err.message;
                importBtn.disabled = false;
            }
        }, 1000);
    });

    async function refreshContacts() {
        try {
            const params  = new URLSearchParams(window.location.search);
            const page    = params.get('page') || '1';
            const perPage = params.get('per_page') || '10';
            const filters = params.get('filters') || '';
            let url = '/contacts-partial?page=' + page + '&per_page=' + perPage;
            if (filters) url += '&filters=' + encodeURIComponent(filters);
            const res  = await fetch(url);
            const html = await res.text();
            document.getElementById('contacts-table-wrap').innerHTML = html;
        } catch (e) {}
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
</script>`;

    res.send(layout('Kontakty – Seznam', 'Seznam všech kontaktů.', body));
});

app.get('/contacts-partial', async (req, res) => {
    const page    = parseInt(req.query.page     || '1',  10);
    const perPage = parseInt(req.query.per_page || '10', 10);
    const filters = req.query.filters || '';

    let apiUrl = `${API_URL}/contacts/?paginate=true&page=${page}&per_page=${perPage}`;
    if (filters) apiUrl += `&filters=${encodeURIComponent(filters)}`;

    let data = { items: [], _meta: null };
    try {
        const apiRes = await fetch(apiUrl, { headers: { 'Accept': 'application/json' } });
        data         = await apiRes.json();
    } catch (e) {}

    const contacts = data.items ?? [];
    const rows = contacts.map(c => `
        <tr>
            <td>${escHtml(c.first_name)}</td>
            <td>${escHtml(c.last_name)}</td>
            <td><a href="mailto:${escHtml(c.email)}">${escHtml(c.email)}</a></td>
            <td>
                <a href="/${c.id}">Upravit</a>
                &nbsp;
                <form method="POST" action="/smazat/${c.id}" style="display:inline" onsubmit="return confirm('Opravdu smazat?')">
                    <button type="submit">Smazat</button>
                </form>
            </td>
        </tr>`).join('');

    let parsedFilters = { first_name: '', last_name: '', email: '' };
    if (filters) {
        try {
            const decoded = JSON.parse(Buffer.from(filters, 'base64').toString('utf8'));
            decoded.forEach(f => { parsedFilters[f.f] = f.v; });
        } catch (e) {}
    }

    res.send(`
<table border="1" cellpadding="6" cellspacing="0">
    <thead>
        <tr><th>Jméno</th><th>Příjmení</th><th>E-mail</th><th>Akce</th></tr>
        <tr>
            <th><input type="text" id="filter-first_name" placeholder="Filtrovat..." value="${escHtml(parsedFilters.first_name)}"></th>
            <th><input type="text" id="filter-last_name" placeholder="Filtrovat..." value="${escHtml(parsedFilters.last_name)}"></th>
            <th><input type="text" id="filter-email" placeholder="Filtrovat..." value="${escHtml(parsedFilters.email)}"></th>
            <th><button id="filter-apply">Filtrovat</button></th>
        </tr>
    </thead>
    <tbody>
        ${rows || '<tr><td colspan="4">Žádné kontakty nenalezeny.</td></tr>'}
    </tbody>
</table>
<br>
<div>${paginationHtml(data._meta, perPage, filters)}</div>`);
});

app.post('/import', upload.single('file'), async (req, res) => {
    const form = new FormData();
    form.append('file', req.file.buffer, {
        filename:    req.file.originalname,
        contentType: req.file.mimetype,
    });

    const apiRes = await fetch(`${API_URL}/contacts/import`, {
        method:  'POST',
        headers: { ...form.getHeaders(), 'Accept': 'application/json' },
        body:    form,
    });

    const data = await apiRes.json();
    res.status(apiRes.status).json(data);
});

app.get('/import/:id', async (req, res) => {
    const apiRes = await fetch(`${API_URL}/imports/${req.params.id}`, {
        headers: { 'Accept': 'application/json' }
    });
    const data = await apiRes.json();
    res.status(apiRes.status).json(data);
});

app.get('/import/:id/failures', async (req, res) => {
    const apiRes = await fetch(`${API_URL}/contacts/import/${req.params.id}/failures`, {
        headers: { 'Accept': 'application/json' }
    });
    const data = await apiRes.json();
    res.status(apiRes.status).json(data);
});

app.get('/novy-kontakt', (req, res) => {
    res.send(layout('Nový kontakt', 'Vytvoření nového kontaktu.', createForm()));
});

function createForm(old = {}, errors = {}) {
    return `
<h1>Nový kontakt</h1>
<p><a href="/">« Zpět na seznam</a></p>
${renderErrors(errors)}
<form method="POST" action="/novy-kontakt">
    <table border="0" cellpadding="6">
        <tr><td><label>Jméno *</label></td><td>
            <input type="text" name="first_name" value="${escHtml(old.first_name)}">
            ${fieldError(errors, 'first_name')}
        </td></tr>
        <tr><td><label>Příjmení *</label></td><td>
            <input type="text" name="last_name" value="${escHtml(old.last_name)}">
            ${fieldError(errors, 'last_name')}
        </td></tr>
        <tr><td><label>E-mail *</label></td><td>
            <input type="text" name="email" value="${escHtml(old.email)}" required>
            ${fieldError(errors, 'email')}
        </td></tr>
        <tr><td colspan="2"><button type="submit">Vytvořit kontakt</button></td></tr>
    </table>
</form>`;
}

app.post('/novy-kontakt', async (req, res) => {
    const apiRes = await fetch(`${API_URL}/contacts/`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(req.body),
    });

    if (apiRes.ok) return res.redirect(302, '/');

    const err = await apiRes.json();
    res.status(422).send(layout('Nový kontakt', 'Vytvoření nového kontaktu.', createForm(req.body, err.errors ?? {})));
});

app.get('/:id', async (req, res, next) => {
    const id = req.params.id;
    if (isNaN(id)) return next();

    const apiRes = await fetch(`${API_URL}/contacts/${id}`, {
        headers: { 'Accept': 'application/json' }
    });

    if (apiRes.status === 404) {
        return res.status(404).send(layout('Nenalezeno', 'Kontakt nenalezen.',
            '<h1>Kontakt nenalezen</h1><p><a href="/">Zpět</a></p>'));
    }

    const contact = await apiRes.json();
    res.send(layout(
        `${contact.first_name} ${contact.last_name} – Editace`,
        `Editace kontaktu ${contact.first_name} ${contact.last_name}.`,
        editForm(contact.id, contact)
    ));
});

function editForm(id, old = {}, errors = {}) {
    return `
<h1>Upravit: ${escHtml(old.first_name)} ${escHtml(old.last_name)}</h1>
<p><a href="/">« Zpět na seznam</a></p>
${renderErrors(errors)}
<form method="POST" action="/${id}">
    <table border="0" cellpadding="6">
        <tr><td><label>Jméno</label></td><td>
            <input type="text" name="first_name" value="${escHtml(old.first_name)}">
            ${fieldError(errors, 'first_name')}
        </td></tr>
        <tr><td><label>Příjmení</label></td><td>
            <input type="text" name="last_name" value="${escHtml(old.last_name)}">
            ${fieldError(errors, 'last_name')}
        </td></tr>
        <tr><td><label>E-mail *</label></td><td>
            <input type="text" name="email" value="${escHtml(old.email)}" required>
            ${fieldError(errors, 'email')}
        </td></tr>
        <tr><td colspan="2"><button type="submit">Uložit</button></td></tr>
    </table>
</form>`;
}

app.post('/:id', async (req, res, next) => {
    const id = req.params.id;
    if (isNaN(id)) return next();

    const apiRes = await fetch(`${API_URL}/contacts/${id}`, {
        method:  'PUT',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body:    JSON.stringify(req.body),
    });

    if (apiRes.ok) return res.redirect(302, `/${id}`);

    const err = await apiRes.json();
    res.status(422).send(layout('Editace – Chyba', 'Editace kontaktu.', editForm(id, req.body, err.errors ?? {})));
});

app.post('/smazat/:id', async (req, res) => {
    await fetch(`${API_URL}/contacts/${req.params.id}`, {
        method:  'DELETE',
        headers: { 'Accept': 'application/json' },
    });
    res.redirect(302, '/');
});

app.listen(PORT, () => console.log(`Frontend running on http://localhost:${PORT}`));