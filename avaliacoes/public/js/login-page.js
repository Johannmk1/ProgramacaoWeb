document.addEventListener('DOMContentLoaded', () => {
  if (!document.body.classList.contains('login-page')) return;

  const form = document.querySelector('[data-login-form]');
  if (!form) return;

  const context = document.body.dataset.loginContext || new URLSearchParams(window.location.search).get('context') || 'default';
  const basePath = document.body.dataset.loginBase || 'src/Controllers';
  const userField = document.getElementById(form.dataset.userField || 'loginUser');
  const passField = document.getElementById(form.dataset.passField || 'loginPass');
  const msgField = document.getElementById(form.dataset.messageField || '');
  const wrapperId = form.dataset.wrapper || null;
  const loginWrapper = wrapperId ? document.getElementById(wrapperId) : form.parentElement;

  const panel = document.getElementById('adminPanel');
  const selectDisp = document.getElementById('admSelectDisp');
  const panelMsg = document.getElementById('admPanelMsg');
  const btnSalvar = document.getElementById('btnAdmSalvarDisp');
  const btnSair = document.getElementById('btnAdmSair');
  const btnVoltar = document.getElementById('btnAdmVoltar');
  const btnAdmLogin = document.getElementById('btnAdmLogin');

  const params = new URLSearchParams(window.location.search);
  const redirectTarget = params.get('redirect') || 'admin/index.php';
  const returnTarget = params.get('from') || document.referrer || 'index.html';

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    autenticar();
  });

  btnSalvar?.addEventListener('click', () => {
    if (!selectDisp) return;
    const code = selectDisp.value;
    if (!code) { panelMsg.textContent = 'Selecione um dispositivo.'; return; }
    localStorage.setItem('deviceCode', code);
    panelMsg.textContent = 'Dispositivo atualizado.';
    setTimeout(() => { panelMsg.textContent = ''; }, 2000);
  });

  btnSair?.addEventListener('click', async () => {
    await fetch(`${basePath}/AuthController.php?action=logout`, { credentials: 'same-origin' });
    window.location.href = 'index.html';
  });

  btnVoltar?.addEventListener('click', () => {
    window.location.href = returnTarget;
  });

  btnAdmLogin?.addEventListener('click', (e) => {
    e.preventDefault();
    autenticar();
  });

  async function autenticar() {
    const username = userField?.value.trim();
    const password = passField?.value || '';
    if (!username || !password) {
      exibirMensagem('Usuário e senha são obrigatórios');
      return;
    }
    exibirMensagem('');
    try {
      const resp = await fetch(`${basePath}/AuthController.php?action=login`, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ username, password }),
      });
      const data = await resp.json();
      if (data.status === 'success') {
        if (panel) {
          mostrarPainel();
        } else {
          window.location.href = redirectTarget;
        }
      } else {
        exibirMensagem(data.message || 'Falha de login');
      }
    } catch (err) {
      exibirMensagem('Erro de rede');
    }
  }

  function exibirMensagem(texto) {
    if (msgField) msgField.textContent = texto || '';
  }

  async function loadDispositivos() {
    if (!selectDisp) return;
    selectDisp.innerHTML = '<option value="">Carregando...</option>';
    try {
      const resp = await fetch(`${basePath}/DispositivoController.php?action=publicos&ativos=1`, { credentials: 'same-origin' });
      const rows = await resp.json();
      if (Array.isArray(rows)) {
        const saved = localStorage.getItem('deviceCode') || '';
        selectDisp.innerHTML = rows.map(d => `<option value="${d.codigo}" ${saved === d.codigo ? 'selected' : ''}>${d.nome}${d.setor_nome ? ` (${d.setor_nome})` : ''}</option>`).join('') || '<option value="">Nenhum dispositivo</option>';
      } else {
        selectDisp.innerHTML = '<option value="">Nenhum dispositivo</option>';
      }
    } catch (err) {
      selectDisp.innerHTML = '<option value="">Erro ao carregar</option>';
    }
  }

  function mostrarPainel() {
    if (loginWrapper) loginWrapper.style.display = 'none';
    panel?.style?.setProperty('display', 'block');
    loadDispositivos();
  }

  if (context === 'admin') {
    // painel só aparece após login, botões já configurados acima
  }
});
