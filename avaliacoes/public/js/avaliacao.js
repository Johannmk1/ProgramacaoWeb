document.addEventListener('DOMContentLoaded', () => {
  initAvaliacao();
});

async function initAvaliacao() {
  await App.loadTheme();

  const url = new URL(window.location.href);
  const fromQuery = url.searchParams.get('device');
  const device = App.getDeviceCode() || fromQuery;
  if (!device) {
    window.location.href = 'index.html';
    return;
  }
  if (!App.getDeviceCode() && device) App.setDeviceCode(device);

  const form = document.getElementById('formAvaliacao');
  const box = document.getElementById('perguntaAtual');
  const progresso = document.getElementById('progresso');
  const feedback = document.getElementById('feedback');
  const feedbackWrapper = document.getElementById('feedbackWrapper');
  const btnEnviar = document.getElementById('btnEnviar');
  const btnCancelar = document.getElementById('btnCancelar');
  const introTexto = document.getElementById('introTexto');
  const overlay = document.getElementById('overlayInatividade');
  const btnContinuar = document.getElementById('btnContinuar');
  const btnCancelarPrompt = document.getElementById('btnCancelarPrompt');
  const countdownEl = document.getElementById('countdown');

  let perguntas = [];
  let respostas = {};
  let idx = 0;
  let idleTimer = null;
  let promptTimer = null;

  const cleanupTimers = () => {
    clearTimeout(idleTimer);
    clearInterval(promptTimer);
    promptTimer = null;
  };

  const showPrompt = () => {
    if (!overlay) return;
    overlay.style.display = 'flex';
    let remaining = 15;
    if (countdownEl) countdownEl.textContent = String(remaining);
    clearInterval(promptTimer);
    promptTimer = setInterval(() => {
      remaining--;
      if (countdownEl) countdownEl.textContent = String(remaining);
      if (remaining <= 0) {
        cleanupTimers();
        window.location.href = 'index.html';
      }
    }, 1000);
  };

  const resetIdle = () => {
    clearTimeout(idleTimer);
    idleTimer = setTimeout(showPrompt, 60000);
  };

  const hidePrompt = () => {
    if (overlay) overlay.style.display = 'none';
    resetIdle();
  };

  document.addEventListener('click', resetIdle, { passive: true });
  document.addEventListener('keydown', resetIdle, { passive: true });
  document.addEventListener('touchstart', resetIdle, { passive: true });
  btnContinuar?.addEventListener('click', hidePrompt);
  btnCancelarPrompt?.addEventListener('click', () => {
    cleanupTimers();
    window.location.href = 'index.html';
  });
  resetIdle();

  btnCancelar?.addEventListener('click', () => {
    cleanupTimers();
    window.location.href = 'index.html';
  });

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const faltantes = perguntas.filter((p) => !(p.id in respostas));
    if (faltantes.length > 0) {
      App.toast('Responda todas as perguntas antes de enviar.', 'warn');
      return;
    }
    const body = {
      respostas,
      feedback: feedback ? (feedback.value.trim() || null) : null,
      device,
    };
    try {
      const resp = await App.api.salvarAvaliacao(body);
      const ok = resp && resp.status === 'success';
      App.toast(resp.message || 'Avaliação enviada.', ok ? 'success' : 'error');
      if (ok) {
        cleanupTimers();
        window.location.href = 'obrigado.html';
      }
    } catch (err) {
      App.toast('Falha ao enviar sua avaliação.', 'error');
    }
  });

  const totalSteps = () => (perguntas.length || 0) + 1; // inclui etapa de feedback

  const updateProgress = (currentStep) => {
    if (!progresso) return;
    const total = totalSteps();
    const bounded = Math.min(Math.max(currentStep, 1), total);
    progresso.textContent = `${bounded}/${total}`;
  };

  const showSubmitArea = () => {
    feedbackWrapper?.classList.remove('hidden');
    btnEnviar?.classList.remove('hidden');
  };

  const hideFeedbackArea = () => {
    feedbackWrapper?.classList.add('hidden');
    btnEnviar?.classList.add('hidden');
  };

  const renderFeedbackStep = () => {
    updateProgress(totalSteps());
    introTexto?.classList.add('hidden');
    if (box) {
      box.innerHTML = '';
      if (feedbackWrapper) {
        box.appendChild(feedbackWrapper);
      } else {
        box.innerHTML = '<p>Compartilhe um feedback adicional (opcional) antes de enviar.</p>';
      }
    }
    showSubmitArea();
  };

  const render = () => {
    introTexto?.classList.remove('hidden');
    if (!perguntas.length) {
      renderFeedbackStep();
      return;
    }
    if (idx >= perguntas.length) {
      renderFeedbackStep();
      return;
    }
    const pergunta = perguntas[idx];
    box.innerHTML = '';
    const label = document.createElement('div');
    label.className = 'label';
    label.textContent = pergunta.texto;
    const grid = document.createElement('div');
    grid.className = 'score-grid';
    for (let score = 0; score <= 10; score++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'nps-btn';
      btn.dataset.score = String(score);
      btn.textContent = String(score);
      btn.addEventListener('click', () => {
        respostas[pergunta.id] = score;
        idx++;
        resetIdle();
        render();
      });
      grid.appendChild(btn);
    }
    box.appendChild(label);
    box.appendChild(grid);
    updateProgress(idx + 1);
    hideFeedbackArea();
  };

  box.innerHTML = '<p>Carregando perguntas...</p>';

  try {
    const list = await App.api.perguntas(device);
    perguntas = Array.isArray(list) ? list : [];
    idx = 0;
    respostas = {};
    hideFeedbackArea();
    render();
  } catch (err) {
    box.innerHTML = '<p>Erro ao carregar perguntas.</p>';
  }
}
