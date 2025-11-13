const apiSetoresAdm = '../../src/Controllers/AdminController.php?resource=setores';
const apiSetorPerguntas = '../../src/Controllers/AdminController.php?resource=setor_perguntas';
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });
const tbodySet = document.getElementById('tbody');
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

function loadSetoresAdm() {
  fetch(apiSetoresAdm, withCreds())
    .then((r) => r.json())
    .then((rows) => {
      tbodySet.innerHTML = '';
      rows.forEach((r) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id}</td>
          <td contenteditable data-field="nome">${r.nome}</td>
          <td>${r.status ? 'Ativo' : 'Inativo'}</td>
          <td class="row-actions">
            <button data-act="save">Salvar</button>
            <button data-act="toggle">${r.status ? 'Desativar' : 'Ativar'}</button>
            <button data-act="del">Excluir</button>
          </td>`;
        tr.dataset.id = r.id;
        tbodySet.appendChild(tr);
      });
      if (selSetorMap) {
        selSetorMap.innerHTML = rows.map(r => `<option value="${r.id}">${r.nome}</option>`).join('');
        if (rows.length > 0) loadPerguntasMap();
      }
    })
    .catch(() => {
      tbodySet.innerHTML = '<tr><td colspan="4">Falha ao carregar</td></tr>';
    });
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
        loadSetoresAdm();
      } else {
        flashSet('Erro ao criar', false);
      }
    });
});

tbodySet.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const nom = tr.querySelector('[data-field="nome"]').textContent.trim();
    fetch(apiSetoresAdm + '&id=' + id, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ nome: nom }),
    }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Atualizado');
          loadSetoresAdm();
        } else {
          flashSet('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const current = tr.children[2].textContent.includes('Ativo');
    fetch(apiSetoresAdm + '&id=' + id, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: !current }),
    }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Status alterado');
          loadSetoresAdm();
        } else {
          flashSet('Erro ao alterar', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir permanentemente?')) return;
    fetch(apiSetoresAdm + '&id=' + id + '&hard=1', withCreds({ method: 'DELETE' }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashSet('Excluído');
          loadSetoresAdm();
        } else {
          flashSet('Erro ao excluir', false);
        }
      });
  }
});

loadSetoresAdm();

function loadPerguntasMap() {
  const id = Number(selSetorMap.value);
  if (!id) { listaPerguntasMap.innerHTML = '<p>Selecione um setor.</p>'; return; }
  listaPerguntasMap.innerHTML = '<p>Carregando perguntas...</p>';
  fetch(`${apiSetorPerguntas}&id_setor=${id}`, withCreds())
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
