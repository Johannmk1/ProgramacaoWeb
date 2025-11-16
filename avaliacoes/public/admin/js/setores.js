const apiSetoresAdm = '../../src/Controllers/AdminController.php?resource=setores';
const apiSetorPerguntas = '../../src/Controllers/AdminController.php?resource=setor_perguntas';
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });
const tabelaSet = document.getElementById('setoresTable');
const msgSet = document.getElementById('msg');
const nomeSet = document.getElementById('nome');
const statusSet = document.getElementById('status');
const selSetorMap = document.getElementById('selSetorMap');
const listaPerguntasMap = document.getElementById('listaPerguntasMap');
const msgMap = document.getElementById('msgMap');

function flashSet(m, ok = true) {
  msgSet.textContent = m;
  msgSet.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msgSet.textContent = ''), 2500);
}

function renderTabelaSetores() {
  if (!tabelaSet) return;
  tabelaSet.innerHTML = '<div class="table-placeholder">Carregando tabela...</div>';
  fetch(`${apiSetoresAdm}&format=html`, withCreds({ cache: 'no-store' }))
    .then((r) => {
      if (!r.ok) throw new Error('Falha ao carregar');
      return r.text();
    })
    .then((html) => {
      tabelaSet.innerHTML = html;
    })
    .catch(() => {
      tabelaSet.innerHTML = '<div class="table-placeholder">Falha ao carregar tabela.</div>';
    });
}

function loadSetorOptions() {
  if (!selSetorMap) return Promise.resolve();
  const current = selSetorMap.value;
  selSetorMap.innerHTML = '<option value="">Carregando setores...</option>';
  return fetch(apiSetoresAdm, withCreds({ cache: 'no-store' }))
    .then((r) => r.json())
    .then((rows) => {
      if (!Array.isArray(rows) || rows.length === 0) {
        selSetorMap.innerHTML = '<option value="">Cadastre um setor</option>';
        listaPerguntasMap.innerHTML = '<p>Cadastre um setor antes de mapear perguntas.</p>';
        return;
      }
      let optionsHtml = '';
      rows.forEach((r) => {
        const selected = String(r.id) === current ? 'selected' : '';
        optionsHtml += `<option value="${r.id}" ${selected}>${r.nome}</option>`;
      });
      selSetorMap.innerHTML = optionsHtml;
      if (!selSetorMap.value && rows.length) {
        selSetorMap.value = String(rows[0].id);
      }
      loadPerguntasMap();
    })
    .catch(() => {
      selSetorMap.innerHTML = '<option value="">Erro ao carregar setores</option>';
      listaPerguntasMap.innerHTML = '<p>Erro ao carregar setores.</p>';
    });
}

function reloadSetores() {
  renderTabelaSetores();
  loadSetorOptions();
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = { nome: nomeSet.value.trim(), status: statusSet.checked };
  fetch(apiSetoresAdm, withCreds({
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  }))
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashSet('Criado com sucesso');
        nomeSet.value = '';
        statusSet.checked = true;
        reloadSetores();
      } else {
        flashSet('Erro ao criar', false);
      }
    });
});

tabelaSet?.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  if (!tr || !tr.dataset.id) return;
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const nomField = tr.querySelector('[data-field="nome"]');
    const nom = nomField ? nomField.textContent.trim() : '';
    fetch(`${apiSetoresAdm}&id=${id}`, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nome: nom }),
    }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Atualizado');
          reloadSetores();
        } else {
          flashSet('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const statusCell = tr.querySelector('[data-field="status"]');
    const current = statusCell ? statusCell.dataset.status === '1' : false;
    fetch(`${apiSetoresAdm}&id=${id}`, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: !current }),
    }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Status alterado');
          reloadSetores();
        } else {
          flashSet('Erro ao alterar', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir permanentemente?')) return;
    fetch(`${apiSetoresAdm}&id=${id}&hard=1`, withCreds({ method: 'DELETE' }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Excluído');
          reloadSetores();
        } else {
          flashSet('Erro ao excluir', false);
        }
      });
  }
});

reloadSetores();

function loadPerguntasMap() {
  const id = Number(selSetorMap.value);
  if (!id) { listaPerguntasMap.innerHTML = '<p>Selecione um setor.</p>'; return; }
  listaPerguntasMap.innerHTML = '<p>Carregando perguntas...</p>';
  fetch(`${apiSetorPerguntas}&id_setor=${id}`, withCreds({ cache: 'no-store' }))
    .then(r => r.json())
    .then(rows => {
      listaPerguntasMap.innerHTML = rows.map(p => `
        <label class="label inline-field" style="display:block;">
          <input type="checkbox" value="${p.id}" ${p.vinculada ? 'checked' : ''} />
          ${p.texto}
        </label>
      `).join('');
    })
    .catch(() => { listaPerguntasMap.innerHTML = '<p>Erro ao carregar perguntas</p>'; });
}

selSetorMap?.addEventListener('change', loadPerguntasMap);

document.getElementById('btnSalvarMap')?.addEventListener('click', () => {
  const id = Number(selSetorMap.value);
  const ids = Array.from(listaPerguntasMap.querySelectorAll('input[type="checkbox"]:checked')).map(i => Number(i.value));
  fetch(apiSetorPerguntas, withCreds({
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ id_setor: id, ids_perguntas: ids })
  }))
    .then(r=>r.json())
    .then(d=>{ msgMap.textContent = d.status==='success' ? 'Mapeamento salvo' : 'Erro ao salvar'; setTimeout(()=>msgMap.textContent='',2500); })
    .catch(()=>{ msgMap.textContent='Erro ao salvar'; setTimeout(()=>msgMap.textContent='',2500); });
});
