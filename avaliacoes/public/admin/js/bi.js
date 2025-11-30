document.addEventListener('DOMContentLoaded', () => {
  const API_URL = '../../src/Controllers/AdminController.php?resource=bi';
  const numberFormatter = new Intl.NumberFormat('pt-BR');
  const charts = { nps: null, volume: null, setores: null, perguntas: null };
  const dom = {
    btnAtualizar: document.getElementById('btnAtualizar'),
    msg: document.getElementById('msgBi'),
    kpiNps: document.getElementById('kpiNps'),
    kpiTotal: document.getElementById('kpiTotal'),
    kpiTempo: document.getElementById('kpiTempo'),
    kpiSetor: document.getElementById('kpiSetor'),
    filtroSetor: document.getElementById('filtroSetor'),
    filtroDispositivo: document.getElementById('filtroDispositivo'),
    filtroInicio: document.getElementById('filtroInicio'),
    filtroFim: document.getElementById('filtroFim'),
    filtroPergunta: document.getElementById('filtroPergunta'),
    sentPromotores: document.getElementById('sentPromotores'),
    sentNeutros: document.getElementById('sentNeutros'),
    sentDetratores: document.getElementById('sentDetratores'),
    sentPromotoresPct: document.getElementById('sentPromotoresPct'),
    sentNeutrosPct: document.getElementById('sentNeutrosPct'),
    sentDetratoresPct: document.getElementById('sentDetratoresPct'),
    listaFeedbacks: document.getElementById('listaFeedbacks'),
    feedbackResumo: document.getElementById('feedbackResumo'),
    btnFeedbackPrev: document.getElementById('btnFeedbackPrev'),
    btnFeedbackNext: document.getElementById('btnFeedbackNext'),
  };

  const comentariosState = {
    page: 1,
    perPage: 5,
    total: 0,
    totalPages: 1,
    hasNext: false,
    hasPrev: false,
    items: [],
    loading: false,
  };

  const escapeHtml = (value = '') =>
    String(value).replace(/[&<>"']/g, (ch) => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
    })[ch]);

  function formatTempo(segundos) {
    if (!Number.isFinite(segundos) || segundos <= 0) return '--';
    const totalSegundos = Math.floor(segundos);
    if (totalSegundos >= 3600) {
      const horas = Math.floor(totalSegundos / 3600);
      const minutos = Math.floor((totalSegundos % 3600) / 60);
      return `${horas}h ${String(minutos).padStart(2, '0')}m`;
    }
    const min = Math.floor(totalSegundos / 60);
    const seg = totalSegundos % 60;
    return `${min}m ${String(seg).padStart(2, '0')}s`;
  }

  function formatarDataHora(raw) {
    if (!raw) return '--';
    const normalized = raw.replace(' ', 'T');
    const data = new Date(normalized);
    if (Number.isNaN(data.getTime())) return raw;
    return data.toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
  }

  function ensureDefaultPeriod() {
    if (!dom.filtroInicio || !dom.filtroFim) return;
    const hoje = new Date();
    const fim = dom.filtroFim.value || hoje.toISOString().slice(0, 10);
    let inicio = dom.filtroInicio.value;
    if (!inicio) {
      const calc = new Date(fim);
      calc.setDate(calc.getDate() - 29);
      inicio = calc.toISOString().slice(0, 10);
    } else if (inicio > fim) {
      const calc = new Date(fim);
      calc.setDate(calc.getDate() - 29);
      inicio = calc.toISOString().slice(0, 10);
    }
    dom.filtroInicio.value = inicio;
    dom.filtroFim.value = fim;
  }

  function setLoadingState(isLoading) {
    if (!dom.btnAtualizar) return;
    dom.btnAtualizar.disabled = isLoading;
    dom.btnAtualizar.textContent = isLoading ? 'Atualizando...' : 'Atualizar dados';
  }

  function updateCommentsNavButtons(isLoading = false) {
    const nextLabel = `Próximas ${comentariosState.perPage}`;
    if (dom.btnFeedbackPrev) {
      dom.btnFeedbackPrev.disabled = isLoading || !comentariosState.hasPrev;
    }
    if (dom.btnFeedbackNext) {
      dom.btnFeedbackNext.disabled = isLoading || !comentariosState.hasNext;
      dom.btnFeedbackNext.textContent = isLoading ? 'Carregando...' : nextLabel;
    }
  }

  function setMessage(text, ok = true) {
    if (!dom.msg) return;
    dom.msg.textContent = text || '';
    dom.msg.style.color = ok ? '#0f172a' : '#b91c1c';
  }

  function renderComentariosPlaceholder(text) {
    if (dom.listaFeedbacks) {
      dom.listaFeedbacks.innerHTML = `<div class="feedback-placeholder">${text}</div>`;
    }
    if (dom.feedbackResumo) dom.feedbackResumo.textContent = '';
    updateCommentsNavButtons(true);
  }

  function serializeFilters() {
    const params = new URLSearchParams();
    if (dom.filtroInicio?.value) params.set('inicio', dom.filtroInicio.value);
    if (dom.filtroFim?.value) params.set('fim', dom.filtroFim.value);
    if (dom.filtroSetor?.value) params.set('setor', dom.filtroSetor.value);
    if (dom.filtroDispositivo?.value) params.set('dispositivo', dom.filtroDispositivo.value);
    if (dom.filtroPergunta?.value) params.set('pergunta', dom.filtroPergunta.value);
    return params;
  }

  function fetchDataset(page = 1) {
    const params = serializeFilters();
    params.set('page_textos', page);
    params.set('per_page_textos', comentariosState.perPage);
    const url = `${API_URL}&${params.toString()}`;
    return fetch(url, { credentials: 'same-origin', cache: 'no-store' }).then((resp) => {
      if (!resp.ok) throw new Error('Erro ao carregar');
      return resp.json();
    });
  }

  function renderKPIs(dataset) {
    const kpis = dataset?.kpis || {};
    if (dom.kpiNps) dom.kpiNps.textContent = Number.isFinite(kpis.nps) ? `${kpis.nps.toFixed(1)} pts` : '--';
    if (dom.kpiTotal) dom.kpiTotal.textContent = numberFormatter.format(kpis.total ?? 0);
    if (dom.kpiTempo) dom.kpiTempo.textContent = formatTempo(kpis.tempoMedio);
    if (dom.kpiSetor) dom.kpiSetor.textContent = kpis.setorDestaque || '--';
  }

  function renderSentimento(dataset) {
    const valores = Array.isArray(dataset?.volume?.valores) ? dataset.volume.valores : [];
    const total = valores.reduce((acc, val) => acc + (Number(val) || 0), 0);
    const [promotores = 0, neutros = 0, detratores = 0] = valores.map((v) => Number(v) || 0);
    const setValor = (node, value) => { if (node) node.textContent = numberFormatter.format(value); };
    const setPercent = (node, value) => {
      if (!node) return;
      node.textContent = total ? `${((value / total) * 100).toFixed(1)}% do total` : '--';
    };
    setValor(dom.sentPromotores, promotores);
    setPercent(dom.sentPromotoresPct, promotores);
    setValor(dom.sentNeutros, neutros);
    setPercent(dom.sentNeutrosPct, neutros);
    setValor(dom.sentDetratores, detratores);
    setPercent(dom.sentDetratoresPct, detratores);
  }

  function renderCharts(dataset) {
    renderNpsChart(dataset?.npsSeries);
    renderVolumeChart(dataset?.volume);
    renderSetoresChart(dataset?.chartSetores);
    renderPerguntasChart(dataset?.chartPerguntas);
  }

  function renderNpsChart(series) {
    const ctx = document.getElementById('chartNps');
    if (!ctx || !window.Chart) return;
    const labels = series?.labels || [];
    const valores = series?.valores || [];
    const config = {
      type: 'line',
      data: {
        labels,
        datasets: [
          {
            label: 'NPS',
            data: valores,
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.15)',
            fill: true,
            tension: 0.35,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, suggestedMin: -100, suggestedMax: 100 } },
        plugins: { legend: { display: false } },
      },
    };
    if (charts.nps) {
      charts.nps.data.labels = config.data.labels;
      charts.nps.data.datasets = config.data.datasets;
      charts.nps.update();
    } else {
      charts.nps = new Chart(ctx, config);
    }
  }

  function renderVolumeChart(volume) {
    const ctx = document.getElementById('chartVolume');
    if (!ctx || !window.Chart) return;
    const labels = volume?.labels || [];
    const valores = volume?.valores || [];
    const config = {
      type: 'doughnut',
      data: {
        labels,
        datasets: [
          {
            data: valores,
            backgroundColor: ['#16a34a', '#fbbf24', '#ef4444'],
          },
        ],
      },
      options: {
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { usePointStyle: true } } },
      },
    };
    if (charts.volume) {
      charts.volume.data.labels = config.data.labels;
      charts.volume.data.datasets = config.data.datasets;
      charts.volume.update();
    } else {
      charts.volume = new Chart(ctx, config);
    }
  }

  function renderSetoresChart(rows) {
    const ctx = document.getElementById('chartSetores');
    if (!ctx || !window.Chart) return;
    const dados = Array.isArray(rows) ? rows : [];
    const labels = dados.map((row) => row.nome);
    const nps = dados.map((row) => Number(row.nps) || 0);
    const totals = dados.map((row) => Number(row.total) || 0);
    const config = {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'NPS',
            data: nps,
            backgroundColor: 'rgba(99, 102, 241, 0.7)',
            borderColor: '#6366f1',
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: { beginAtZero: true, suggestedMin: -100, suggestedMax: 100 },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctxTooltip) => {
                const value = ctxTooltip.parsed.y !== undefined ? ctxTooltip.parsed.y : ctxTooltip.parsed;
                const total = totals[ctxTooltip.dataIndex] || 0;
                return [`${value.toFixed(1)} pts`, `${numberFormatter.format(total)} respostas`];
              },
            },
          },
          legend: { display: false },
        },
      },
    };
    if (charts.setores) {
      charts.setores.data.labels = config.data.labels;
      charts.setores.data.datasets = config.data.datasets;
      charts.setores.update();
    } else {
      charts.setores = new Chart(ctx, config);
    }
  }

  function renderPerguntasChart(rows) {
    const ctx = document.getElementById('chartPerguntas');
    if (!ctx || !window.Chart) return;
    const dados = Array.isArray(rows) ? rows : [];
    const labels = dados.map((row) => row.pergunta);
    const medias = dados.map((row) => Number(row.media) || 0);
    const totals = dados.map((row) => Number(row.respostas) || 0);
    const config = {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Média (0 a 10)',
            data: medias,
            backgroundColor: 'rgba(14,165,233,0.7)',
            borderColor: '#0ea5e9',
            borderWidth: 1,
          },
        ],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          x: { beginAtZero: true, suggestedMax: 10 },
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: (ctxTooltip) => {
                const value = ctxTooltip.parsed.x !== undefined ? ctxTooltip.parsed.x : ctxTooltip.parsed;
                const total = totals[ctxTooltip.dataIndex] || 0;
                return [`Média ${value.toFixed(2)}`, `${numberFormatter.format(total)} respostas`];
              },
            },
          },
          legend: { display: false },
        },
      },
    };
    if (charts.perguntas) {
      charts.perguntas.data.labels = config.data.labels;
      charts.perguntas.data.datasets = config.data.datasets;
      charts.perguntas.update();
    } else {
      charts.perguntas = new Chart(ctx, config);
    }
  }

  function renderCombos(dataset) {
    const filters = dataset?.filters || {};
    if (dom.filtroSetor) {
      const setores = dataset?.setores || [];
      const options = ['<option value="">Todos os setores</option>'];
      setores.forEach((setor) => {
        const value = String(setor.id ?? '');
        options.push(`<option value="${escapeHtml(value)}">${escapeHtml(setor.nome)}</option>`);
      });
      dom.filtroSetor.innerHTML = options.join('');
      if (filters.setor) dom.filtroSetor.value = String(filters.setor);
    }
    if (dom.filtroDispositivo) {
      const dispositivos = dataset?.dispositivos || [];
      const options = ['<option value="">Todos os dispositivos</option>'];
      dispositivos.forEach((disp) => {
        const legenda = disp.setor_nome ? ` (${disp.setor_nome})` : '';
        options.push(
          `<option value="${escapeHtml(disp.codigo)}">${escapeHtml(disp.nome + legenda)}</option>`,
        );
      });
      dom.filtroDispositivo.innerHTML = options.join('');
      if (filters.dispositivo) dom.filtroDispositivo.value = filters.dispositivo;
    }
    if (dom.filtroPergunta) {
      const perguntasLista = dataset?.perguntasFiltro || [];
      const options = ['<option value="">Todas as perguntas</option>'];
      perguntasLista.forEach((item) => {
        options.push(`<option value="${escapeHtml(String(item.id))}">${escapeHtml(item.texto)}</option>`);
      });
      dom.filtroPergunta.innerHTML = options.join('');
      if (filters.pergunta) dom.filtroPergunta.value = String(filters.pergunta);
    }
  }

  function atualizarUI(dataset) {
    aplicarFiltros(dataset?.filters);
    renderCombos(dataset);
    renderKPIs(dataset);
    renderSentimento(dataset);
    renderCharts(dataset);
  }

  function aplicarFiltros(filters = {}) {
    if (dom.filtroInicio && filters.inicio) dom.filtroInicio.value = filters.inicio;
    if (dom.filtroFim && filters.fim) dom.filtroFim.value = filters.fim;
  }

  function atualizarComentarios(payload) {
    if (!payload) {
      comentariosState.items = [];
      comentariosState.total = 0;
      comentariosState.page = 1;
      comentariosState.totalPages = 1;
      comentariosState.hasNext = false;
      comentariosState.hasPrev = false;
      renderComentarios();
      return;
    }
    comentariosState.perPage = payload.perPage ?? comentariosState.perPage;
    comentariosState.total = payload.total ?? 0;
    comentariosState.page = payload.page ?? comentariosState.page;
    comentariosState.totalPages = Math.max(1, payload.totalPages ?? Math.ceil(comentariosState.total / comentariosState.perPage));
    comentariosState.hasNext = Boolean(payload.hasNext ?? (comentariosState.page < comentariosState.totalPages));
    comentariosState.hasPrev = Boolean(payload.hasPrev ?? (comentariosState.page > 1));
    comentariosState.items = Array.isArray(payload.items) ? payload.items : [];
    renderComentarios();
  }

  function renderComentarios() {
    if (!dom.listaFeedbacks) return;
    if (!comentariosState.items.length) {
      renderComentariosPlaceholder('Nenhuma resposta escrita para os filtros selecionados.');
      return;
    }
    const html = comentariosState.items
      .map((item) => {
        const setor = item.setor ? escapeHtml(item.setor) : 'Setor não informado';
        const pergunta = item.pergunta ? escapeHtml(item.pergunta) : 'Pergunta não informada';
        const texto = escapeHtml(item.texto || '');
        const dispositivoNome = item.dispositivo ? escapeHtml(item.dispositivo) : 'Dispositivo não informado';
        const dispositivoCodigo = item.dispositivoCodigo ? ` (${escapeHtml(item.dispositivoCodigo)})` : '';
        return `
          <article class="feedback-item">
            <header>
              <strong>${setor}</strong>
              <span>${formatarDataHora(item.data)}</span>
            </header>
            <p class="feedback-question">Pergunta: ${pergunta}</p>
            <p class="feedback-text">"${texto}"</p>
            <p class="feedback-meta">Registrado em ${dispositivoNome}${dispositivoCodigo}</p>
          </article>`;
      })
      .join('');
    dom.listaFeedbacks.innerHTML = html;
    if (dom.feedbackResumo) {
      dom.feedbackResumo.textContent = `Mostrando ${numberFormatter.format(
        comentariosState.items.length,
      )} de ${numberFormatter.format(comentariosState.total)} respostas (página ${Math.min(
        comentariosState.page,
        comentariosState.totalPages,
      )} de ${comentariosState.totalPages}).`;
    }
    updateCommentsNavButtons(false);
  }

  function handleAtualizarClick(ev) {
    if (ev) ev.preventDefault();
    setLoadingState(true);
    setMessage('Atualizando dados...');
    renderComentariosPlaceholder('Carregando respostas...');
    fetchDataset(1)
      .then((data) => {
        atualizarUI(data);
        atualizarComentarios(data?.comentarios);
        setMessage('Painel atualizado.');
      })
      .catch((err) => {
        console.error(err);
        setMessage('Falha ao carregar dados. Tente novamente.', false);
        renderComentariosPlaceholder('Não foi possível carregar as respostas.');
      })
      .finally(() => setLoadingState(false));
  }

  function loadComentariosPage(page) {
    if (comentariosState.loading) return;
    comentariosState.loading = true;
    updateCommentsNavButtons(true);
    setMessage('Atualizando respostas...');
    fetchDataset(page)
      .then((data) => {
        atualizarComentarios(data?.comentarios);
        setMessage('Respostas atualizadas.');
      })
      .catch((err) => {
        console.error(err);
        setMessage('Erro ao paginar respostas.', false);
      })
      .finally(() => {
        comentariosState.loading = false;
        updateCommentsNavButtons(false);
      });
  }

  function handleComentariosPrev(ev) {
    if (ev) ev.preventDefault();
    if (!comentariosState.hasPrev) return;
    const target = Math.max(1, comentariosState.page - 1);
    loadComentariosPage(target);
  }

  function handleComentariosNext(ev) {
    if (ev) ev.preventDefault();
    if (!comentariosState.hasNext) return;
    const target = comentariosState.page + 1;
    loadComentariosPage(target);
  }

  dom.btnAtualizar?.addEventListener('click', handleAtualizarClick);
  dom.btnFeedbackPrev?.addEventListener('click', handleComentariosPrev);
  dom.btnFeedbackNext?.addEventListener('click', handleComentariosNext);
  dom.filtroSetor?.addEventListener('change', () => {
    if (dom.filtroPergunta) dom.filtroPergunta.value = '';
  });

  ensureDefaultPeriod();
  renderComentariosPlaceholder('Carregando respostas...');
  handleAtualizarClick();
});
