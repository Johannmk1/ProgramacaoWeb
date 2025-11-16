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
  const btnEnviar = document.getElementById('btnEnviar');
  const btnCancelar = document.getElementById('btnCancelar');
  const formActions = form?.querySelector('.form-actions');
  let btnSalvarTexto = null;
  let avaliacaoEnviada = false;
  const introTexto = document.getElementById('introTexto');
  const overlay = document.getElementById('overlayInatividade');
  const btnContinuar = document.getElementById('btnContinuar');
  const btnCancelarPrompt = document.getElementById('btnCancelarPrompt');
  const countdownEl = document.getElementById('countdown');
  const introBox = document.getElementById('introTexto');

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

  const totalSteps = () => perguntas.length || 0;

  async function finalizarAvaliacao() {
    if (avaliacaoEnviada) return;
    const faltantes = perguntas.filter((p) => !(p.id in respostas) || respostas[p.id] === null || respostas[p.id] === '');
    if (faltantes.length > 0) {
      idx = perguntas.findIndex((p) => p.id === faltantes[0].id);
      return render();
    }
    avaliacaoEnviada = true;
    try {
      const resp = await App.api.salvarAvaliacao({ respostas, device });
      if (resp && resp.status === 'success') {
        cleanupTimers();
        window.location.href = 'obrigado.html';
      } else {
        throw new Error('erro');
      }
    } catch (err) {
      avaliacaoEnviada = false;
      App.toast('Falha ao enviar sua avaliação.', 'error');
    }
  }

  const updateProgress = (currentStep) => {
    if (!progresso) return;
    const total = totalSteps();
    const bounded = Math.min(Math.max(currentStep, 1), total);
    progresso.textContent = `${bounded}/${total}`;
  };

  const renderEstadoIndisponivel = (mensagem) => {
    cleanupTimers();
    btnEnviar?.classList.add('hidden');
    if (progresso) { progresso.textContent = ''; }
    if (introBox) {
      introBox.textContent = 'Não é possível iniciar a avaliação agora.';
      introBox.classList.remove('hidden');
    }
    if (box) {
      box.innerHTML = `
        <div class="mensagem" style="color:#b91c1c; font-weight:600;">
          <p style="margin:0;"><strong>Dispositivo sem perguntas.</strong></p>
          <p style="margin:4px 0 0;">${mensagem}</p>
        </div>`;
    }
  };

  const render = () => {
    introTexto?.classList.remove('hidden');
    if (!perguntas.length) {
      renderEstadoIndisponivel('Este dispositivo ainda não está vinculado a um setor ativo ou não possui perguntas cadastradas. Solicite ajuda a um colaborador para configurar pela área administrativa.');
      return;
    }
    if (idx >= perguntas.length) {
      finalizarAvaliacao();
      return;
    }
    const pergunta = perguntas[idx];
    box.innerHTML = '';
    const label = document.createElement('div');
    label.className = 'label';
    label.textContent = pergunta.texto;
    box.appendChild(label);
    const tipo = String(pergunta.tipo || 'nps').trim().toLowerCase();
    if (tipo === 'texto') {
      const block = document.createElement('div');
      block.className = 'feedback-block';
      const textarea = document.createElement('textarea');
      textarea.className = 'textarea';
      textarea.placeholder = 'Digite sua resposta...';
      textarea.value = respostas[pergunta.id] || '';
      block.appendChild(textarea);
      if (!btnSalvarTexto && formActions) {
        btnSalvarTexto = document.createElement('button');
        btnSalvarTexto.type = 'button';
        btnSalvarTexto.id = 'btnSalvarTexto';
        btnSalvarTexto.className = 'btn';
        btnSalvarTexto.textContent = 'Salvar resposta';
        formActions.insertBefore(btnSalvarTexto, btnEnviar);
      }
      btnSalvarTexto.onclick = () => {
        const valor = textarea.value.trim();
        if (!valor) { App.toast('Informe uma resposta.', 'warn'); return; }
        respostas[pergunta.id] = valor;
        idx++;
        resetIdle();
        render();
      };
      btnSalvarTexto.classList.remove('hidden');
      btnEnviar?.classList.add('hidden');
      box.appendChild(block);
    } else {
      if (btnSalvarTexto) {
        btnSalvarTexto.classList.add('hidden');
        btnSalvarTexto.onclick = null;
      }
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
      box.appendChild(grid);
    }
    updateProgress(idx + 1);
  };

  box.innerHTML = '<p>Carregando perguntas...</p>';

  try {
    const resp = await App.api.perguntas(device);
    if (resp && resp.status === 'empty') {
      renderEstadoIndisponivel(resp.message || 'Nenhuma pergunta disponível para este dispositivo.');
      return;
    }
    const list = Array.isArray(resp) ? resp : (resp && Array.isArray(resp.perguntas) ? resp.perguntas : []);
    perguntas = Array.isArray(list) ? list : [];
    idx = 0;
    respostas = {};
    if (!perguntas.length) {
      renderEstadoIndisponivel('Este dispositivo ainda não está vinculado a um setor ativo ou não possui perguntas cadastradas. Solicite ajuda a um colaborador para configurar pela área administrativa.');
      return;
    }
    render();
  } catch (err) {
    box.innerHTML = '<p>Erro ao carregar perguntas.</p>';
  }
}
