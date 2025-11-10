(function () {
  const BASE = '../src/Controllers';

  function normalizeHex(hex) {
    if (!hex) return null;
    let c = hex.replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    return c.length === 6 ? c : null;
  }

  function contrastColor(hex) {
    const normalized = normalizeHex(hex);
    if (!normalized) return '#0f172a';
    const r = parseInt(normalized.substring(0, 2), 16) / 255;
    const g = parseInt(normalized.substring(2, 4), 16) / 255;
    const b = parseInt(normalized.substring(4, 6), 16) / 255;
    const luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return luma > 0.5 ? '#0f172a' : '#ffffff';
  }

  function mixColor(hexA, hexB, amount = 0.5) {
    const normA = normalizeHex(hexA);
    const normB = normalizeHex(hexB);
    if (!normA || !normB) return hexA || hexB || '#ffffff';
    const w = Math.min(Math.max(amount, 0), 1);
    const rA = parseInt(normA.substring(0, 2), 16);
    const gA = parseInt(normA.substring(2, 4), 16);
    const bA = parseInt(normA.substring(4, 6), 16);
    const rB = parseInt(normB.substring(0, 2), 16);
    const gB = parseInt(normB.substring(2, 4), 16);
    const bB = parseInt(normB.substring(4, 6), 16);
    const r = Math.round(rA * w + rB * (1 - w));
    const g = Math.round(gA * w + gB * (1 - w));
    const b = Math.round(bA * w + bB * (1 - w));
    const toHex = (value) => {
      const s = value.toString(16);
      return s.length === 1 ? `0${s}` : s;
    };
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
  }
  const App = {
    theme: {},
    _themePromise: null,

    normalizeTheme(theme = {}) {
      return {
        primaryColor: theme.primaryColor || '#2563eb',
        secondaryColor: theme.secondaryColor || '#0ea5e9',
        tertiaryColor: theme.tertiaryColor || '#f7f8fa',
        cardMaxWidth: theme.cardMaxWidth || '820px',
      };
    },

    loadTheme() {
      if (this._themePromise) return this._themePromise;
      const applyAndStore = (theme) => {
        this.theme = this.normalizeTheme(theme);
        this.applyTheme();
        return this.theme;
      };
      if (window.__PRELOADED_THEME) {
        this._themePromise = Promise.resolve(applyAndStore(window.__PRELOADED_THEME));
        return this._themePromise;
      }
      const cacheBust = window.__THEME_CACHE_BUST || Date.now();
      this._themePromise = fetch(`config/theme.json?cb=${cacheBust}`, { cache: 'no-store' })
        .then((res) => (res.ok ? res.json() : {}))
        .catch(() => ({}))
        .then(applyAndStore);
      return this._themePromise;
    },

    applyTheme() {
      const root = document.documentElement;
      const setVar = (k, v) => v && root.style.setProperty(k, v);
      const primary = this.theme.primaryColor;
      const secondary = this.theme.secondaryColor;
      const tertiary = this.theme.tertiaryColor;
      const primaryContrast = contrastColor(primary);
      const secondaryContrast = contrastColor(secondary);
      const tertiaryContrast = contrastColor(tertiary);
      setVar('--t-primary', primary);
      setVar('--t-secondary', secondary);
      setVar('--t-bg', tertiary);
      setVar('--t-text', tertiaryContrast);
      setVar('--t-contrast', primaryContrast);
      setVar('--k-card-max', this.theme.cardMaxWidth);
      setVar('--k-bg', tertiary);
      setVar('--k-text', tertiaryContrast);
      setVar('--k-card', tertiary);
      setVar('--k-primary', primary);
      setVar('--k-muted', '#64748b');
      setVar('--k-sub', '#475569');
      setVar('--k-title', tertiaryContrast);
      setVar('--color-bg', tertiary);
      setVar('--color-text', tertiaryContrast);
      setVar('--color-primary', primary);
      setVar('--color-primary-contrast', primaryContrast);
      setVar('--color-secondary', secondary);
      setVar('--color-secondary-contrast', secondaryContrast);
      setVar('--color-tertiary', tertiary);
      setVar('--color-tertiary-contrast', tertiaryContrast);
      setVar('--color-title', tertiaryContrast);
      const feedbackBg = mixColor(secondary, '#ffffff', 0.18);
      const feedbackBorder = mixColor(secondary, '#ffffff', 0.55);
      const feedbackPlaceholder = mixColor(secondary, '#111827', 0.65);
      const feedbackText = contrastColor(feedbackBg);
      setVar('--feedback-bg', feedbackBg);
      setVar('--feedback-border', feedbackBorder);
      setVar('--feedback-placeholder', feedbackPlaceholder);
      setVar('--feedback-text', feedbackText);
    },

    getTheme() { return this.theme; },

    fetchJSON(url, options = {}) {
      return fetch(url, options).then((res) => {
        if (!res.ok) throw new Error('HTTP');
        return res.json();
      });
    },

    api: {
      dispositivos() {
        return App.fetchJSON(`${BASE}/DispositivoController.php?action=publicos&ativos=1`, { cache: 'no-store' });
      },
      perguntas(device) {
        return App.fetchJSON(`${BASE}/AvaliacaoController.php?action=perguntas&device=${encodeURIComponent(device || '')}`, { cache: 'no-store' });
      },
      salvarAvaliacao(body) {
        return App.fetchJSON(`${BASE}/AvaliacaoController.php?action=salvar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body),
        });
      },
      login(username, password) {
        return App.fetchJSON(`${BASE}/AuthController.php?action=login`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ username, password }),
        });
      },
      logout() {
        return fetch(`${BASE}/AuthController.php?action=logout`).then(() => ({}));
      },
    },

    getDeviceCode() {
      if (this._deviceCode) return this._deviceCode;
      const url = new URL(window.location.href);
      const qp = url.searchParams.get('device');
      if (qp) localStorage.setItem('deviceCode', qp);
      this._deviceCode = localStorage.getItem('deviceCode') || qp || null;
      return this._deviceCode;
    },

    setDeviceCode(code) {
      this._deviceCode = code || null;
      if (code) localStorage.setItem('deviceCode', code);
    },

    toast(message, kind = 'info', ms = 2200) {
      const el = document.getElementById('toast');
      if (!el) return;
      el.textContent = message;
      el.className = `toast ${kind}`;
      el.style.display = 'block';
      setTimeout(() => { el.style.display = 'none'; }, ms);
    },
  };

  window.App = App;
  App.loadTheme();
})();



