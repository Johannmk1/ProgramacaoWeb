const apiPerguntas = '../../api/admin/perguntas.php';
const tbodyPerg = document.getElementById('tbody');
const msgPerg = document.getElementById('msg');
const textoPerg = document.getElementById('texto');
const ordemPerg = document.getElementById('ordem');
const statusPerg = document.getElementById('status');

function flashPerg(m, ok = true) {
  msgPerg.textContent = m;
  msgPerg.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msgPerg.textContent = ''), 2500);
}

function loadPerguntas() {
  fetch(apiPerguntas)
    .then((r) => r.json())
    .then((rows) => {
      tbodyPerg.innerHTML = '';
      rows.forEach((r) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${r.id}</td>
          <td contenteditable data-field="texto">${r.texto}</td>
          <td contenteditable data-field="ordem">${r.ordem ?? 0}</td>
          <td>${r.status ? 'Ativa' : 'Inativa'}</td>
          <td class="row-actions">
            <button data-act="save">Salvar</button>
            <button data-act="toggle">${r.status ? 'Desativar' : 'Ativar'}</button>
            <button data-act="del">Excluir</button>
          </td>`;
        tr.dataset.id = r.id;
        tbodyPerg.appendChild(tr);
      });
    })
    .catch(() => {
      tbodyPerg.innerHTML = '<tr><td colspan="5">Falha ao carregar</td></tr>';
    });
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = {
    texto: textoPerg.value.trim(),
    ordem: ordemPerg.value ? Number(ordemPerg.value) : 0,
    status: statusPerg.checked,
  };
  fetch(apiPerguntas, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashPerg('Criada com sucesso');
        textoPerg.value = '';
        ordemPerg.value = '';
        statusPerg.checked = true;
        loadPerguntas();
      } else {
        flashPerg('Erro ao criar', false);
      }
    });
});

tbodyPerg.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const t = tr.querySelector('[data-field="texto"]').textContent.trim();
    const o = Number(tr.querySelector('[data-field="ordem"]').textContent.trim() || '0');
    fetch(apiPerguntas + '?id=' + id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ texto: t, ordem: o }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashPerg('Atualizado');
          loadPerguntas();
        } else {
          flashPerg('Erro ao atualizar', false);
        }
      });
  } else if (act === 'toggle') {
    const current = tr.children[3].textContent.includes('Ativa');
    fetch(apiPerguntas + '?id=' + id, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: !current }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashPerg('Status alterado');
          loadPerguntas();
        } else {
          flashPerg('Erro ao alterar', false);
        }
      });
  } else if (act === 'del') {
    if (!confirm('Excluir permanentemente?')) return;
    fetch(apiPerguntas + '?id=' + id + '&hard=1', { method: 'DELETE' })
      .then((r) => r.json())
      .then((d) => {
        if (d.status === 'success') {
          flashPerg('Excluída');
          loadPerguntas();
        } else {
          flashPerg('Erro ao excluir', false);
        }
      });
  }
});

loadPerguntas();

