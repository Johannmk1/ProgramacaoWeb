document.addEventListener('DOMContentLoaded', () => {
  init();
});

async function init() {
  await App.loadTheme();

  const selector = document.getElementById('seletorDispositivo');
  const select = document.getElementById('selectDispositivo');
  const btnUsar = document.getElementById('btnUsarDispositivo');
  const msg = document.getElementById('mensagemDispositivo');
  const btnComecar = document.getElementById('btnComecar');
  const adminLink = document.getElementById('adminLink');
  const adminDeviceLayer = document.getElementById('adminDeviceLayer');
  const adminDeviceSelect = document.getElementById('adminDeviceSelect');
  const adminDeviceMsg = document.getElementById('adminDeviceMsg');
  const btnAdminSaveDevice = document.getElementById('btnAdminSaveDevice');
  const btnAdminLogoutDevice = document.getElementById('btnAdminLogoutDevice');

  let devicesLoaded = false;
  let adminDevicesLoaded = false;

  const ensureDevice = async () => {
    if (App.getDeviceCode()) {
      selector.style.display = 'none';
      return true;
    }

    selector.style.display = 'flex';
    if (!devicesLoaded) {
      select.innerHTML = '<option>Carregando...</option>';
      try {
        const rows = await App.api.dispositivos();
        if (!Array.isArray(rows) || rows.length === 0) {
          select.innerHTML = '<option value="">Nenhum dispositivo ativo</option>';
        } else {
          select.innerHTML = rows.map((d) => `<option value="${d.codigo}">${d.nome}${d.setor_nome ? ` (${d.setor_nome})` : ''}</option>`).join('');
        }
        devicesLoaded = true;
      } catch (err) {
        select.innerHTML = '<option value="">Erro ao carregar</option>';
        return false;
      }
    }

    const code = select.value || '';
    if (code) {
      App.setDeviceCode(code);
      selector.style.display = 'none';
      return true;
    }

    msg.textContent = 'Selecione um dispositivo para continuar.';
    setTimeout(() => { msg.textContent = ''; }, 1800);
    return false;
  };

  btnComecar?.addEventListener('click', async () => {
    const ok = await ensureDevice();
    if (ok) window.location.href = 'avaliacao.html';
  });

  btnUsar?.addEventListener('click', () => {
    const code = select.value;
    if (!code) { msg.textContent = 'Selecione um dispositivo.'; return; }
    App.setDeviceCode(code);
    msg.textContent = 'Dispositivo definido.';
    setTimeout(() => { msg.textContent = ''; }, 1600);
    selector.style.display = 'none';
    window.location.href = 'avaliacao.html';
  });

  btnAdminSaveDevice?.addEventListener('click', () => {
    if (!adminDeviceSelect) return;
    const code = adminDeviceSelect.value;
    if (!code) { adminDeviceMsg.textContent = 'Selecione um dispositivo.'; return; }
    App.setDeviceCode(code);
    adminDeviceMsg.textContent = 'Dispositivo atualizado.';
    setTimeout(() => {
      adminDeviceMsg.textContent = '';
      hideAdminDeviceLayer();
    }, 1400);
  });

  btnAdminLogoutDevice?.addEventListener('click', async () => {
    await App.api.logout();
    hideAdminDeviceLayer();
  });

  adminLink?.addEventListener('click', (e) => {
    e.preventDefault();
    const ret = `${window.location.pathname}${window.location.search || ''}`;
    const target = new URL(window.location.href);
    target.search = '?admin-device=1';
    target.hash = '';
    const url = new URL('login/login.php', window.location.href);
    url.searchParams.set('context', 'admin');
    url.searchParams.set('redirect', target.toString());
    url.searchParams.set('from', ret);
    window.location.href = url.toString();
  });

  const params = new URLSearchParams(window.location.search);
  if (params.get('admin-device') === '1') {
    showAdminDeviceLayer();
    if (history.replaceState) {
      history.replaceState({}, '', window.location.pathname);
    }
  }

  async function showAdminDeviceLayer() {
    if (!adminDeviceLayer) return;
    adminDeviceLayer.style.display = 'flex';
    if (!adminDevicesLoaded) {
      await loadAdminDevices();
      adminDevicesLoaded = true;
    }
  }

  function hideAdminDeviceLayer() {
    if (adminDeviceLayer) adminDeviceLayer.style.display = 'none';
  }

  async function loadAdminDevices() {
    if (!adminDeviceSelect) return;
    adminDeviceSelect.innerHTML = '<option value="">Carregando...</option>';
    try {
      const rows = await App.api.dispositivos();
      if (Array.isArray(rows) && rows.length > 0) {
        const saved = App.getDeviceCode() || '';
        adminDeviceSelect.innerHTML = rows.map((d) => `<option value="${d.codigo}" ${saved === d.codigo ? 'selected' : ''}>${d.nome}${d.setor_nome ? ` (${d.setor_nome})` : ''}</option>`).join('');
      } else {
        adminDeviceSelect.innerHTML = '<option value="">Nenhum dispositivo</option>';
      }
    } catch (err) {
      adminDeviceSelect.innerHTML = '<option value="">Erro ao carregar</option>';
    }
  }
}
