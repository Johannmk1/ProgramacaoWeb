document.addEventListener('DOMContentLoaded', () => {
  initAdmin();
});

async function initAdmin() {
  await App.loadTheme();

  const sanitizeTarget = (value) => {
    if (!value) return null;
    try {
      const target = new URL(value, window.location.href);
      return target.origin === window.location.origin ? target.href : null;
    } catch (err) {
      return null;
    }
  };

  const url = new URL(window.location.href);
  const fromParam = sanitizeTarget(url.searchParams.get('from'));
  const referrer = sanitizeTarget(document.referrer);
  const storedReturn = sanitizeTarget(sessionStorage.getItem('adminReturnTo'));
  const fallback = sanitizeTarget('index.html') || window.location.href;
  const returnTarget = fromParam || referrer || storedReturn || fallback;
  sessionStorage.setItem('adminReturnTo', returnTarget);

  const loginBox = document.getElementById('adminLogin');
  const panelBox = document.getElementById('adminPanel');
  const inpUser = document.getElementById('admUser');
  const inpPass = document.getElementById('admPass');
  const btnLogin = document.getElementById('btnAdmLogin');
  const btnSalvar = document.getElementById('btnAdmSalvarDisp');
  const btnSair = document.getElementById('btnAdmSair');
  const btnVoltar = document.getElementById('btnAdmVoltar');
  const msg = document.getElementById('admMsg');
  const panelMsg = document.getElementById('admPanelMsg');
  const select = document.getElementById('admSelectDisp');

  const loadDispositivos = async () => {
    select.innerHTML = '<option>Carregando...</option>';
    try {
      const rows = await App.api.dispositivos();
      select.innerHTML = (rows || []).map((d) => `<option value="${d.codigo}" ${App.getDeviceCode() === d.codigo ? 'selected' : ''}>${d.nome}${d.setor_nome ? ` (${d.setor_nome})` : ''}</option>`).join('');
    } catch (err) {
      select.innerHTML = '<option value="">Erro ao carregar</option>';
    }
  };

  btnLogin?.addEventListener('click', async () => {
    msg.textContent = '';
    try {
      const resp = await App.api.login((inpUser.value || '').trim(), inpPass.value || '');
      if (resp.status === 'success') {
        if (fromParam && fromParam.includes('/admin/')) {
          window.location.href = fromParam;
          return;
        }
        loginBox.style.display = 'none';
        panelBox.style.display = 'block';
        loadDispositivos();
      } else {
        msg.textContent = resp.message || 'Falha de login';
      }
    } catch (err) {
      msg.textContent = 'Erro de rede';
    }
  });

  btnSalvar?.addEventListener('click', () => {
    const code = select.value;
    if (!code) return;
    App.setDeviceCode(code);
    panelMsg.textContent = 'Dispositivo atualizado';
    setTimeout(() => { panelMsg.textContent = ''; }, 2000);
  });

  btnSair?.addEventListener('click', async () => {
    await App.api.logout();
    window.location.href = 'index.html';
  });

  btnVoltar?.addEventListener('click', () => {
    if (window.history.length > 1) {
      window.history.back();
      return;
    }
    const target = sanitizeTarget(sessionStorage.getItem('adminReturnTo')) || returnTarget;
    window.location.href = target;
  });
}
