const apiPerguntas = '../../src/Controllers/AdminController.php?resource=perguntas';
const tabelaPerg = document.getElementById('perguntasTable');
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });
const msgPerg = document.getElementById('msg');
const textoPerg = document.getElementById('texto');
const ordemPerg = document.getElementById('ordem');
const statusPerg = document.getElementById('status');
const tipoPerg = document.getElementById('tipo');

function flashPerg(m, ok = true) {
  msgPerg.textContent = m;
  msgPerg.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msgPerg.textContent = ''), 2500);
}

function loadPerguntas() {
  if (!tabelaPerg) return;
  tabelaPerg.innerHTML = '<div class="table-placeholder">Carregando tabela...</div>';
  fetch(`${apiPerguntas}&format=html`, withCreds({ cache: 'no-store' }))
    .then((r) => {
      if (!r.ok) throw new Error('Falha ao carregar');
      return r.text();
    })
    .then((html) => {
      tabelaPerg.innerHTML = html;
    })
    .catch(() => {
      tabelaPerg.innerHTML = `
        <table>
          <tbody><tr><td colspan="5">Falha ao carregar.</td></tr></tbody>
        </table>`;
    });
}

document.getElementById('btnAdd').addEventListener('click', () => {
  const body = {
    texto: textoPerg.value.trim(),
    ordem: ordemPerg.value ? Number(ordemPerg.value) : 0,
    status: statusPerg.checked,
    tipo: tipoPerg.value || 'nps',
  };
  fetch(apiPerguntas, withCreds({
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  }))
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashPerg('Criada com sucesso');
        textoPerg.value = '';
        ordemPerg.value = '';
        statusPerg.checked = true;
        tipoPerg.value = 'nps';
        loadPerguntas();
      } else {
        flashPerg('Erro ao criar', false);
      }
    });
});

if (tabelaPerg) {
tabelaPerg.addEventListener('click', (e) => {
  const btn = e.target.closest('button');
  if (!btn) return;
  const tr = btn.closest('tr');
  if (!tr || !tr.dataset.id) return;
  const id = Number(tr.dataset.id);
  const act = btn.dataset.act;
  if (act === 'save') {
    const textoField = tr.querySelector('[data-field="texto"]');
    const ordemField = tr.querySelector('[data-field="ordem"]');
    const tipoField = tr.querySelector('[data-field="tipo"]');
    const t = textoField ? textoField.textContent.trim() : '';
    const o = Number(ordemField ? ordemField.textContent.trim() : '0');
    const tipo = tipoField ? tipoField.value : 'nps';
    fetch(`${apiPerguntas}&id=${id}`, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ texto: t, ordem: o, tipo }),
    }))
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
    const statusCell = tr.querySelector('[data-field="status"]');
    const current = statusCell ? statusCell.dataset.status === '1' : false;
    fetch(`${apiPerguntas}&id=${id}`, withCreds({
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: !current }),
    }))
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
    fetch(`${apiPerguntas}&id=${id}&hard=1`, withCreds({ method: 'DELETE' }))
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
}

loadPerguntas();
