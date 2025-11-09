const apiDisp = '../../src/Controllers/AdminController.php?resource=dispositivos';
const apiSetAdm = '../../src/Controllers/AdminController.php?resource=setores';
const tbodyDisp = document.getElementById('tbody');
const msgDisp = document.getElementById('msg');
const nomeDisp = document.getElementById('nome');
const codigoDisp = document.getElementById('codigo');
const idSetorDisp = document.getElementById('id_setor');
const statusDisp = document.getElementById('status');

function flashDisp(m, ok = true) {
  msgDisp.textContent = m;
  msgDisp.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msgDisp.textContent = ''), 2500);
}

function loadSetoresSelect() {
  return fetch(apiSetAdm + '?ativos=1')
    .then((r) => r.json())
    .then((rows) => {
      idSetorDisp.innerHTML = '<option value="">Sem setor</option>' + rows.map((s) => `<option value="${s.id}">${s.nome}</option>`).join('');
    })
    .catch(() => {
      idSetorDisp.innerHTML = '<option value="">Sem setor</option>';
    });
}

function loadDispositivos() {
  fetch(apiDisp)
    .then((r) => r.json())
    .then((rows) => {
      tbodyDisp.innerHTML = '';
      rows.forEach((r) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id}</td>
          <td contenteditable data-field="nome">${r.nome}</td>
          <td contenteditable data-field="codigo">${r.codigo}</td>
          <td>
            <select data-field="id_setor">
              <option value="">Sem setor</option>
            </select>
            <small class="muted">${r.setor_nome || ''}</small>
          </td>
          <td>${r.status ? 'Ativo' : 'Inativo'}</td>
          <td class="row-actions">
            <button data-act="save">Salvar</button>
            <button data-act="toggle">${r.status ? 'Desativar' : 'Ativar'}</button>
            <button data-act="del">Excluir</button>
          </td>`;
        tr.dataset.id = r.id;
        tbodyDisp.appendChild(tr);
        fetch(apiSetAdm + '?ativos=1')
          .then((x) => x.json())
          .then((setores) => {
            const sel = tr.querySelector('select[data-field="id_setor"]');
            sel.innerHTML = '<option value="">Sem setor</option>' + setores.map((s) => `<option value="${s.id}" ${r.id_setor == s.id ? 'selected' : ''}>${s.nome}</option>`).join('');
          });
      });
    })
    .catch(() => {
      tbodyDisp.innerHTML = '<tr><td colspan="6">Falha ao carregar</td></tr>';
    });
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = {
    nome: nomeDisp.value.trim(),
    codigo: codigoDisp.value.trim(),
    id_setor: idSetorDisp.value ? Number(idSetorDisp.value) : null,
    status: statusDisp.checked,
  };
  fetch(apiDisp, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashDisp('Criado com sucesso');
        nomeDisp.value = '';
        codigoDisp.value = '';
        statusDisp.checked = true;
        idSetorDisp.value = '';
        loadDispositivos();
      } else {
        flashDisp('Erro ao criar (código único?)', false);
      }
    });
});

tbodyDisp.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const nom = tr.querySelector('[data-field="nome"]').textContent.trim();
    const cod = tr.querySelector('[data-field="codigo"]').textContent.trim();
    const set = tr.querySelector('select[data-field="id_setor"]').value;
    const payload = { nome: nom, codigo: cod, id_setor: set ? Number(set) : null };
    fetch(apiDisp + '&id=' + id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Atualizado');
          loadDispositivos();
        } else {
          flashDisp('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const current = tr.children[4].textContent.includes('Ativo');
    fetch(apiDisp + '&id=' + id, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: !current }) })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Status alterado');
          loadDispositivos();
        } else {
          flashDisp('Erro ao alterar', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir permanentemente?')) return;
    fetch(apiDisp + '&id=' + id + '&hard=1', { method: 'DELETE' })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Excluído');
          loadDispositivos();
        } else {
          flashDisp('Erro ao excluir', false);
        }
      });
  }
});

loadSetoresSelect().then(loadDispositivos);

