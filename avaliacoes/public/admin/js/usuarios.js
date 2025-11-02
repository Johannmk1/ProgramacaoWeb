document.addEventListener('DOMContentLoaded', () => {
  const api = '../../api/admin/usuarios.php';
  const tbody = document.getElementById('tbody');
  const msg = document.getElementById('msg');
  const username = document.getElementById('username');
  const password = document.getElementById('password');
  const ativo = document.getElementById('ativo');

  function flash(text, ok = true) {
    msg.textContent = text;
    msg.style.color = ok ? '#065f46' : '#b91c1c';
    setTimeout(() => (msg.textContent = ''), 2500);
  }

  function load() {
    fetch(api, { cache: 'no-store' })
      .then(r => r.json())
      .then(rows => {
        tbody.innerHTML = '';
        (rows || []).forEach(u => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td>${u.id}</td>
            <td contenteditable data-field="username">${u.username}</td>
            <td>${u.status ? 'Ativo' : 'Inativo'}</td>
            <td>${(u.created_at || '').toString().replace('T',' ').slice(0,19)}</td>
            <td class="row-actions">
              <button data-act="save">Salvar</button>
              <button data-act="toggle">${u.status ? 'Desativar' : 'Ativar'}</button>
              <button data-act="reset">Resetar Senha</button>
            </td>`;
          tr.dataset.id = u.id;
          tbody.appendChild(tr);
        });
      })
      .catch(() => { tbody.innerHTML = '<tr><td colspan="5">Falha ao carregar</td></tr>'; });
  }

  document.getElementById('btnAdd').addEventListener('click', () => {
    const body = {
      username: username.value.trim(),
      password: password.value,
      status: !!ativo.checked,
    };
    if (!body.username || !body.password) { flash('Usuário e senha são obrigatórios', false); return; }
    fetch(api, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) })
      .then(r => r.json())
      .then(d => { if (d.status === 'success') { flash('Criado com sucesso'); username.value=''; password.value=''; ativo.checked=true; load(); } else { flash('Erro ao criar', false); } })
      .catch(() => flash('Erro ao criar', false));
  });

  tbody.addEventListener('click', (e) => {
    const btn = e.target.closest('button'); if (!btn) return;
    const tr = btn.closest('tr'); const id = Number(tr.dataset.id);
    const act = btn.dataset.act;
    if (act === 'save') {
      const user = tr.querySelector('[data-field="username"]').textContent.trim();
      fetch(`${api}?id=${id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ username: user }) })
        .then(r=>r.json()).then(d=>{ if (d.status==='success'){ flash('Atualizado'); load(); } else { flash('Erro ao atualizar', false); } });
    } else if (act === 'toggle') {
      const isActive = tr.children[2].textContent.includes('Ativo');
      fetch(`${api}?id=${id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ status: !isActive }) })
        .then(r=>r.json()).then(d=>{ if (d.status==='success'){ flash('Status alterado'); load(); } else { flash('Erro ao alterar', false); } });
    } else if (act === 'reset') {
      const pwd = prompt('Nova senha para o usuário:');
      if (!pwd) return;
      fetch(`${api}?id=${id}`, { method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ password: pwd }) })
        .then(r=>r.json()).then(d=>{ if (d.status==='success'){ flash('Senha atualizada'); } else { flash('Erro ao atualizar senha', false); } });
    }
  });

  load();
});

