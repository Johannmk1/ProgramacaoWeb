// =========================================================
// SISTEMA DE AVALIAÇÃO — FRONT-END (Tablet / Kiosk Version)
// =========================================================

(() => {
  'use strict';

  // ---------- Atalhos ----------
  const $ = (id) => document.getElementById(id);
  const root = document.documentElement;

  // ---------- Elementos ----------
  const el = {
    banner: $('bannerImage'),
    telaInicio: $('telaInicio'),
    telaSelecao: $('telaSelecaoDispositivo'),
    selectDisp: $('selectDispositivo'),
    btnUsarDisp: $('btnUsarDispositivo'),
    btnComecar: $('btnComecar'),
    form: $('formAvaliacao'),
    perguntaBox: $('perguntaAtual'),
    progresso: $('progresso'),
    feedbackLabel: $('feedbackLabel'),
    feedback: $('feedback'),
    btnEnviar: $('btnEnviar'),
    overlayPrompt: $('overlayPrompt'),
    telaContinuar: $('telaContinuar'),
    btnContinuar: $('btnContinuar'),
    btnCancelar: $('btnCancelar'),
    btnCancelarAvaliacao: $('btnCancelarAvaliacao'),
    countdown: $('countdown'),
    toast: $('toast'),
  };

  // ---------- Estado ----------
  let deviceCode = null;
  let perguntas = [];
  let respostas = {};
  let idx = 0;
  let lastActivity = Date.now();
  let idleTimer = null;
  let promptTimer = null;

  // ---------- Helpers ----------
  const show = (el) => el && (el.style.display = 'block');
  const hide = (el) => el && (el.style.display = 'none');
  const touch = () => { lastActivity = Date.now(); };
  const fetchJSON = (url, opts = {}) => fetch(url, opts).then(r => r.json());
  const toastMsg = (msg, kind = 'info', ms = 2200) => {
    if (!el.toast) return;
    el.toast.textContent = msg;
    el.toast.className = `toast ${kind}`;
    el.toast.style.display = 'block';
    setTimeout(() => { el.toast.style.display = 'none'; }, ms);
  };

  // =========================================================
  // 1. Tema Dinâmico
  // =========================================================
  fetch('config/theme.json', { cache: 'no-store' })
    .then(r => (r.ok ? r.json() : {}))
    .catch(() => ({}))
    .then((theme) => {
      const url = new URL(window.location.href);
      const o = {
        primary: url.searchParams.get('primary'),
        secondary: url.searchParams.get('secondary'),
        bg: url.searchParams.get('bg'),
        text: url.searchParams.get('text'),
        contrast: url.searchParams.get('contrast'),
        banner: url.searchParams.get('banner') || theme.bannerImage,
      };
      const setVar = (k, v) => v && root.style.setProperty(k, v);
      setVar('--t-primary', o.primary || theme.primaryColor);
      setVar('--t-secondary', o.secondary || theme.secondaryColor);
      setVar('--t-bg', o.bg || theme.bgColor);
      setVar('--t-text', o.text || theme.textColor);
      setVar('--t-contrast', o.contrast || theme.primaryContrast);
      if (o.banner && el.banner) {
        el.banner.src = o.banner;
        el.banner.style.display = 'block';
      }
    });

  // =========================================================
  // 2. Dispositivo
  // =========================================================
  const url = new URL(window.location.href);
  const qp = url.searchParams.get('device');
  if (qp) localStorage.setItem('deviceCode', qp);
  deviceCode = localStorage.getItem('deviceCode') || qp || null;

  function loadDevices() {
    if (!el.selectDisp) return;
    el.selectDisp.innerHTML = '<option>Carregando...</option>';

    fetchJSON('../src/Controllers/DispositivoController.php?action=publicos&ativos=1', { cache: 'no-store' })
      .then((rows) => {
        if (!Array.isArray(rows) || rows.length === 0) {
          el.selectDisp.innerHTML = '<option value="">Nenhum dispositivo ativo</option>';
          return;
        }
        el.selectDisp.innerHTML = rows
          .map((d) =>
            `<option value="${d.codigo}">${d.nome}${d.setor_nome ? ` (${d.setor_nome})` : ''}</option>`
          )
          .join('');
      })
      .catch(() => {
        el.selectDisp.innerHTML = '<option value="">Erro ao carregar</option>';
      });
  }

  // =========================================================
  // 3. Perguntas e Renderização
  // =========================================================
  function loadPerguntas() {
    el.perguntaBox.innerHTML = '<p>Carregando perguntas...</p>';
    return fetchJSON(`../src/Controllers/AvaliacaoController.php?action=perguntas&device=${encodeURIComponent(deviceCode)}`, { cache: 'no-store' })
      .then((list) => {
        perguntas = Array.isArray(list) ? list : [];
        respostas = {};
        idx = 0;
        render();
      })
      .catch(() => {
        el.perguntaBox.innerHTML = '<p>Erro ao carregar perguntas.</p>';
      });
  }

  function render() {
    if (idx < 0) idx = 0;

    // Todas respondidas
    if (idx >= perguntas.length) {
      el.perguntaBox.innerHTML = '<p>Todas as perguntas foram respondidas. Você pode enviar a avaliação.</p>';
      el.progresso && (el.progresso.textContent = `${perguntas.length}/${perguntas.length}`);
      el.feedbackLabel && el.feedbackLabel.classList.remove('hidden');
      el.feedback && el.feedback.classList.remove('hidden');
      el.btnEnviar && el.btnEnviar.classList.remove('hidden');
      return;
    }

    // Oculta elementos de feedback durante a resposta
    el.feedbackLabel && el.feedbackLabel.classList.add('hidden');
    el.feedback && el.feedback.classList.add('hidden');
    el.btnEnviar && el.btnEnviar.classList.add('hidden');

    const p = perguntas[idx];
    const curr = respostas[p.id];
    const btns = Array.from({ length: 11 }, (_, i) =>
      `<button type="button" class="nps-btn${curr === i ? ' is-selected' : ''}" data-score="${i}">${i}</button>`
    ).join('');

    el.perguntaBox.innerHTML = `
      <div class="pergunta nps">
        <label class="label">${p.texto}</label>
        <div class="score-grid">${btns}</div>
      </div>`;

    el.progresso && (el.progresso.textContent = `${idx + 1}/${perguntas.length}`);

    el.perguntaBox.querySelectorAll('button[data-score]').forEach((b) => {
      b.addEventListener('click', () => {
        respostas[p.id] = Number(b.dataset.score);
        touch();
        idx++;
        render();
      });
    });
  }

  // =========================================================
  // 4. Controle de Inatividade
  // =========================================================
  function startIdle() {
    clearInterval(idleTimer);
    idleTimer = setInterval(() => {
      if (Date.now() - lastActivity >= 60000) showPrompt();
    }, 1000);
  }

  function stopIdle() {
    clearInterval(idleTimer);
    idleTimer = null;
  }

  function showPrompt() {
    stopIdle();
    let s = 15;
    if (el.countdown) el.countdown.textContent = String(s);
    if (el.overlayPrompt) el.overlayPrompt.style.display = 'flex';
    clearInterval(promptTimer);
    promptTimer = setInterval(() => {
      s--;
      if (el.countdown) el.countdown.textContent = String(s);
      if (s <= 0) cancelar();
    }, 1000);
  }

  function hidePrompt() {
    if (el.overlayPrompt) el.overlayPrompt.style.display = 'none';
    clearInterval(promptTimer);
    promptTimer = null;
    touch();
    startIdle();
  }

  function cancelar() {
    clearInterval(promptTimer);
    promptTimer = null;
    stopIdle();
    if (el.overlayPrompt) el.overlayPrompt.style.display = 'none';
    if (el.form) el.form.reset();
    respostas = {};
    perguntas = [];
    idx = 0;
    if (el.perguntaBox) el.perguntaBox.innerHTML = '';
    hide(el.form);
    show(el.telaInicio);
  }

  // =========================================================
  // 5. Eventos Globais e Fluxo Inicial
  // =========================================================
  ['input', 'change', 'click', 'keydown', 'mousemove', 'touchstart'].forEach((evt) => {
    document.addEventListener(evt, () => {
      if (el.form && el.form.style.display !== 'none') touch();
    }, { passive: true });
  });

  // Inicialização
  show(el.telaInicio);
  if (!deviceCode) loadDevices();

  // =========================================================
  // 6. Botões e Ações
  // =========================================================
  el.btnUsarDisp?.addEventListener('click', () => {
    const code = el.selectDisp?.value || '';
    if (!code) return;
    localStorage.setItem('deviceCode', code);
    deviceCode = code;
    hide(el.telaSelecao);
    show(el.telaInicio);
  });

  el.btnComecar?.addEventListener('click', async () => {
    if (!deviceCode) {
      hide(el.telaInicio);
      show(el.telaSelecao);
      return;
    }
    await loadPerguntas();
    hide(el.telaInicio);
    show(el.form);
    touch();
    startIdle();
  });

  el.btnContinuar?.addEventListener('click', hidePrompt);
  el.btnCancelar?.addEventListener('click', cancelar);
  el.btnCancelarAvaliacao?.addEventListener('click', cancelar);

  // =========================================================
  // 7. Envio do Formulário
  // =========================================================
  el.form?.addEventListener('submit', (e) => {
    e.preventDefault();

    const falt = perguntas.filter((p) => !(p.id in respostas));
    if (falt.length > 0) {
      toastMsg('Responda todas as perguntas antes de enviar.', 'warn');
      return;
    }

    const body = {
      respostas,
      feedback: (el.feedback?.value.trim() || null),
      device: deviceCode || null,
    };

    fetch('../src/Controllers/AvaliacaoController.php?action=salvar', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
      .then((r) => r.json())
      .then((d) => {
        toastMsg(d.message || 'Avaliação enviada.', d.status === 'success' ? 'success' : 'error');
        if (d.status === 'success') cancelar();
      })
      .catch(() => toastMsg('Falha ao enviar sua avaliação.', 'error'));
  });

  // =========================================================
  // 8. Painel rápido de Admin (⚙️)
  // =========================================================
  const adm = { btn: document.getElementById('btnAdmin'), overlay: document.getElementById('adminOverlay'), tela: document.getElementById('telaAdmin'), login: document.getElementById('adminLogin'), panel: document.getElementById('adminPanel'), user: document.getElementById('admUser'), pass: document.getElementById('admPass'), btnLogin: document.getElementById('btnAdmLogin'), msg: document.getElementById('admMsg'), sel: document.getElementById('admSelectDisp'), btnSalvar: document.getElementById('btnAdmSalvarDisp'), btnSair: document.getElementById('btnAdmSair'), panelMsg: document.getElementById('admPanelMsg'), };

  function loadAdminDevices() {
    if (!adm.sel) return;
    adm.sel.innerHTML = '';
    fetch('../src/Controllers/DispositivoController.php?action=publicos&ativos=1', { cache: 'no-store' })
      .then(r => r.json())
      .then(rows => {
        adm.sel.innerHTML = (rows || []).map(d =>
          `<option value="${d.codigo}" ${deviceCode===d.codigo?'selected':''}>${d.nome}${d.setor_nome?` (${d.setor_nome})`:''}</option>`
        ).join('');
      })
      .catch(() => { adm.sel.innerHTML = '<option value="">Erro ao carregar</option>'; });
  }

  adm.btn?.addEventListener('click', () => { if (!adm.overlay) return; adm.overlay.style.display='flex'; if (adm.login) adm.login.style.display='block'; if (adm.panel) adm.panel.style.display='none'; });

  adm.btnLogin?.addEventListener('click', () => {
    if (adm.msg) adm.msg.textContent = '';
    fetch('../src/Controllers/AuthController.php?action=login', {
      method:'POST', headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ username: (adm.user?.value||'').trim(), password: adm.pass?.value || '' })
    })
      .then(r=>r.json())
      .then(d=>{
        if (d.status==='success') {
          if (adm.login) adm.login.style.display='none';
          if (adm.panel) adm.panel.style.display='block';
          loadAdminDevices();
        } else {
          if (adm.msg) adm.msg.textContent = d.message || 'Falha de login';
        }
      })
      .catch(()=>{ if (adm.msg) adm.msg.textContent='Erro de rede'; });
  });

  adm.btnSalvar?.addEventListener('click', () => {
    const code = adm.sel?.value; if (!code) return;
    localStorage.setItem('deviceCode', code); deviceCode = code;
    if (adm.panelMsg) { adm.panelMsg.textContent = 'Dispositivo atualizado'; setTimeout(()=>{ adm.panelMsg.textContent=''; }, 2000); }
  });

  adm.btnSair?.addEventListener('click', () => {
    fetch('../src/Controllers/AuthController.php?action=logout').finally(()=>{
      if (adm.login) adm.login.style.display='block';
      if (adm.panel) adm.panel.style.display='none';
      if (adm.overlay) adm.overlay.style.display='none';
    });
  });
})();

