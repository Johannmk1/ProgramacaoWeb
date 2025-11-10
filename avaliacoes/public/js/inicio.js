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

  let devicesLoaded = false;

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
}
