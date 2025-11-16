document.addEventListener('DOMContentLoaded', () => {
  if (!document.body.classList.contains('login-page')) return;

  const form = document.querySelector('[data-login-form]');
  if (!form) return;

  const context = document.body.dataset.loginContext || new URLSearchParams(window.location.search).get('context') || 'default';
  const basePath = document.body.dataset.loginBase || 'src/Controllers';
  const userField = document.getElementById(form.dataset.userField || 'loginUser');
  const passField = document.getElementById(form.dataset.passField || 'loginPass');
  const msgField = document.getElementById(form.dataset.messageField || '');
  const btnVoltar = document.getElementById('btnAdmVoltar');
  const btnAdmLogin = document.getElementById('btnAdmLogin');

  const params = new URLSearchParams(window.location.search);
  const redirectTarget = params.get('redirect') || '../admin/index.php';
  const returnTarget = params.get('from') || document.referrer || 'index.html';

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    autenticar();
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
        window.location.href = redirectTarget;
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

});
