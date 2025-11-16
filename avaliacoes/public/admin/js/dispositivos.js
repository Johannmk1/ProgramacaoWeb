const apiDisp = '../../src/Controllers/AdminController.php?resource=dispositivos';
const apiSetAdm = '../../src/Controllers/AdminController.php?resource=setores';
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });
const tabelaDisp = document.getElementById('dispositivosTable');
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
  return fetch(`${apiSetAdm}&ativos=1`, withCreds({ cache: 'no-store' }))
    .then((r) => r.json())
    .then((rows) => {
      const options = ['<option value="">Sem setor</option>'];
      rows.forEach((s) => options.push(`<option value="${s.id}">${s.nome}</option>`));
      idSetorDisp.innerHTML = options.join('');
    })
    .catch(() => {
      idSetorDisp.innerHTML = '<option value="">Sem setor</option>';
    });
}

function renderTabelaDispositivos() {
  if (!tabelaDisp) return;
  tabelaDisp.innerHTML = '<div class="table-placeholder">Carregando tabela...</div>';
  fetch(`${apiDisp}&format=html`, withCreds({ cache: 'no-store' }))
    .then((r) => {
      if (!r.ok) throw new Error('Falha');
      return r.text();
    })
    .then((html) => {
      tabelaDisp.innerHTML = html;
    })
    .catch(() => {
      tabelaDisp.innerHTML = '<div class="table-placeholder">Falha ao carregar tabela.</div>';
    });
}

function reloadDispositivos() {
  renderTabelaDispositivos();
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = {
    nome: nomeDisp.value.trim(),
    codigo: codigoDisp.value.trim(),
    id_setor: idSetorDisp.value ? Number(idSetorDisp.value) : null,
    status: statusDisp.checked,
  };
  fetch(apiDisp, withCreds({ method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }))
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashDisp('Criado com sucesso');
        nomeDisp.value = '';
        codigoDisp.value = '';
        statusDisp.checked = true;
        idSetorDisp.value = '';
        reloadDispositivos();
      } else {
        flashDisp('Erro ao criar (código único?)', false);
      }
    });
});

tabelaDisp?.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  if (!tr || !tr.dataset.id) return;
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const nomeField = tr.querySelector('[data-field="nome"]');
    const codigoField = tr.querySelector('[data-field="codigo"]');
    const setorSelect = tr.querySelector('select[data-field="id_setor"]');
    const payload = {
      nome: nomeField ? nomeField.textContent.trim() : '',
      codigo: codigoField ? codigoField.textContent.trim() : '',
      id_setor: setorSelect && setorSelect.value ? Number(setorSelect.value) : null,
    };
    fetch(`${apiDisp}&id=${id}`, withCreds({ method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Atualizado');
          reloadDispositivos();
        } else {
          flashDisp('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const statusCell = tr.querySelector('[data-field="status"]');
    const current = statusCell ? statusCell.dataset.status === '1' : false;
    fetch(`${apiDisp}&id=${id}`, withCreds({ method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: !current }) }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Status alterado');
          reloadDispositivos();
        } else {
          flashDisp('Erro ao alterar', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir permanentemente?')) return;
    fetch(`${apiDisp}&id=${id}&hard=1`, withCreds({ method: 'DELETE' }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashDisp('Excluído');
          reloadDispositivos();
        } else {
          flashDisp('Erro ao excluir', false);
        }
      });
  }
});

loadSetoresSelect().then(reloadDispositivos);

