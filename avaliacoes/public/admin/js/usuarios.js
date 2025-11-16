const apiUsuarios = '../../src/Controllers/AdminController.php?resource=usuarios';
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });
const tabelaUsuarios = document.getElementById('usuariosTable');
const msgUsuarios = document.getElementById('msg');
const username = document.getElementById('username');
const password = document.getElementById('password');
const ativo = document.getElementById('ativo');

function flashUsuarios(text, ok = true) {
  msgUsuarios.textContent = text;
  msgUsuarios.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msgUsuarios.textContent = ''), 2500);
}

function renderTabelaUsuarios() {
  if (!tabelaUsuarios) return;
  tabelaUsuarios.innerHTML = '<div class="table-placeholder">Carregando tabela...</div>';
  fetch(`${apiUsuarios}&format=html`, withCreds({ cache: 'no-store' }))
    .then((r) => {
      if (!r.ok) throw new Error('Falha ao carregar');
      return r.text();
    })
    .then((html) => {
      tabelaUsuarios.innerHTML = html;
    })
    .catch(() => {
      tabelaUsuarios.innerHTML = '<div class="table-placeholder">Falha ao carregar tabela.</div>';
    });
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = {
    username: username.value.trim(),
    password: password.value,
    status: !!ativo.checked,
  };
  if (!body.username || !body.password) {
    flashUsuarios('Usuário e senha são obrigatórios', false);
    return;
  }
  fetch(apiUsuarios, withCreds({ method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) }))
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashUsuarios('Criado com sucesso');
        username.value = '';
        password.value = '';
        ativo.checked = true;
        renderTabelaUsuarios();
      } else {
        flashUsuarios('Erro ao criar', false);
      }
    })
    .catch(() => flashUsuarios('Erro ao criar', false));
});

tabelaUsuarios?.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  if (!tr || !tr.dataset.id) return;
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const userField = tr.querySelector('[data-field="username"]');
    const user = userField ? userField.textContent.trim() : '';
    fetch(`${apiUsuarios}&id=${id}`, withCreds({ method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ username: user }) }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashUsuarios('Atualizado');
          renderTabelaUsuarios();
        } else {
          flashUsuarios('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const statusCell = tr.querySelector('[data-field="status"]');
    const isActive = statusCell ? statusCell.dataset.status === '1' : false;
    fetch(`${apiUsuarios}&id=${id}`, withCreds({ method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: !isActive }) }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashUsuarios('Status alterado');
          renderTabelaUsuarios();
        } else {
          flashUsuarios('Erro ao alterar', false);
        }
      });
  } else if (act === 'reset') {
    const pwd = prompt('Nova senha para o usuário:');
    if (!pwd) return;
    fetch(`${apiUsuarios}&id=${id}`, withCreds({ method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ password: pwd }) }))
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashUsuarios('Senha atualizada');
        } else {
          flashUsuarios('Erro ao atualizar senha', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir usuário permanentemente?')) return;
    fetch(`${apiUsuarios}&id=${id}&hard=1`, withCreds({ method: 'DELETE' }))
      .then(async (r) => {
        let data = {};
        try { data = await r.json(); } catch (e) { /* ignore */ }
        if (r.ok && data.status === 'success') {
          flashUsuarios('Usuário excluído');
          renderTabelaUsuarios();
          return;
        }
        const msg = data.message || 'Erro ao excluir';
        flashUsuarios(msg, false);
        alert(msg);
      })
      .catch(() => {
        flashUsuarios('Erro ao excluir', false);
        alert('Erro ao excluir');
      });
  }
});

renderTabelaUsuarios();
