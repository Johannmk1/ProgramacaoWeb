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
  const feedbackLabel = document.getElementById('feedbackLabel');
  const feedback = document.getElementById('feedback');
  const btnEnviar = document.getElementById('btnEnviar');
  const btnCancelar = document.getElementById('btnCancelar');

  let perguntas = [];
  let respostas = {};
  let idx = 0;

  btnCancelar?.addEventListener('click', () => {
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
      feedback: (feedback.value.trim() || null),
      device,
    };
    try {
      const resp = await App.api.salvarAvaliacao(body);
      const ok = resp && resp.status === 'success';
      App.toast(resp.message || 'Avaliação enviada.', ok ? 'success' : 'error');
      if (ok) window.location.href = 'obrigado.html';
    } catch (err) {
      App.toast('Falha ao enviar sua avaliação.', 'error');
    }
  });

  const render = () => {
    if (!perguntas.length) {
      box.innerHTML = '<p>Nenhuma pergunta disponível.</p>';
      return;
    }
    if (idx >= perguntas.length) {
      box.innerHTML = '<p>Todas as perguntas foram respondidas. Você pode enviar a avaliação.</p>';
      if (progresso) progresso.textContent = `${perguntas.length}/${perguntas.length}`;
      feedbackLabel.classList.remove('hidden');
      feedback.classList.remove('hidden');
      btnEnviar.classList.remove('hidden');
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
        render();
      });
      grid.appendChild(btn);
    }
    box.appendChild(label);
    box.appendChild(grid);
    if (progresso) progresso.textContent = `${idx + 1}/${perguntas.length}`;
  };

  box.innerHTML = '<p>Carregando perguntas...</p>';
  try {
    const list = await App.api.perguntas(device);
    perguntas = Array.isArray(list) ? list : [];
    render();
  } catch (err) {
    box.innerHTML = '<p>Erro ao carregar perguntas.</p>';
  }
}

