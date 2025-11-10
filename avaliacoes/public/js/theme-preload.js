(function () {
  var defaults = {
    primaryColor: '#2563eb',
    secondaryColor: '#0ea5e9',
    tertiaryColor: '#f7f8fa',
    cardMaxWidth: '820px'
  };

  function normalizeHex(hex) {
    if (!hex) return null;
    var c = hex.replace('#', '');
    if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
    return c.length === 6 ? c : null;
  }

  function contrastColor(hex) {
    var norm = normalizeHex(hex);
    if (!norm) return '#0f172a';
    var r = parseInt(norm.substr(0, 2), 16) / 255;
    var g = parseInt(norm.substr(2, 2), 16) / 255;
    var b = parseInt(norm.substr(4, 2), 16) / 255;
    var luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
    return luma > 0.5 ? '#0f172a' : '#ffffff';
  }

  function mixColor(hexA, hexB, amount) {
    if (amount === void 0) amount = 0.5;
    var normA = normalizeHex(hexA);
    var normB = normalizeHex(hexB);
    if (!normA || !normB) return hexA || hexB || '#ffffff';
    var w = Math.min(Math.max(amount, 0), 1);
    var rA = parseInt(normA.substr(0, 2), 16);
    var gA = parseInt(normA.substr(2, 2), 16);
    var bA = parseInt(normA.substr(4, 2), 16);
    var rB = parseInt(normB.substr(0, 2), 16);
    var gB = parseInt(normB.substr(2, 2), 16);
    var bB = parseInt(normB.substr(4, 2), 16);
    var r = Math.round(rA * w + rB * (1 - w));
    var g = Math.round(gA * w + gB * (1 - w));
    var b = Math.round(bA * w + bB * (1 - w));
    var toHex = function (value) {
      var s = value.toString(16);
      return s.length === 1 ? '0' + s : s;
    };
    return "#" + toHex(r) + toHex(g) + toHex(b);
  }

  function normalizeTheme(theme) {
    theme = theme || {};
    return {
      primaryColor: theme.primaryColor || defaults.primaryColor,
      secondaryColor: theme.secondaryColor || defaults.secondaryColor,
      tertiaryColor: theme.tertiaryColor || defaults.tertiaryColor,
      cardMaxWidth: theme.cardMaxWidth || defaults.cardMaxWidth,
    };
  }

  function applyNormalized(theme) {
    var root = document.documentElement;
    var setVar = function (k, v) { if (v) root.style.setProperty(k, v); };
    var primary = theme.primaryColor;
    var secondary = theme.secondaryColor;
    var tertiary = theme.tertiaryColor;
    var primaryContrast = contrastColor(primary);
    var secondaryContrast = contrastColor(secondary);
    var tertiaryContrast = contrastColor(tertiary);

    setVar('--t-primary', primary);
    setVar('--t-secondary', secondary);
    setVar('--t-bg', tertiary);
    setVar('--t-text', tertiaryContrast);
    setVar('--t-contrast', primaryContrast);
    setVar('--k-card-max', theme.cardMaxWidth || defaults.cardMaxWidth);
    setVar('--k-bg', tertiary);
    setVar('--k-text', tertiaryContrast);
    setVar('--k-primary', primary);
    setVar('--k-card', tertiary);
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
    var feedbackBg = mixColor(secondary, '#ffffff', 0.18);
    var feedbackBorder = mixColor(secondary, '#ffffff', 0.55);
    var feedbackPlaceholder = mixColor(secondary, '#111827', 0.65);
    var feedbackText = contrastColor(feedbackBg);
    setVar('--feedback-bg', feedbackBg);
    setVar('--feedback-border', feedbackBorder);
    setVar('--feedback-placeholder', feedbackPlaceholder);
    setVar('--feedback-text', feedbackText);
  }

  function useTheme(theme) {
    var normalized = normalizeTheme(theme);
    window.__PRELOADED_THEME = normalized;
    applyNormalized(normalized);
  }

  try {
    var cacheBust = Date.now().toString();
    window.__THEME_CACHE_BUST = cacheBust;
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'config/theme.json?cb=' + cacheBust, false);
    xhr.setRequestHeader('Cache-Control', 'no-cache');
    xhr.send(null);
    if (xhr.status >= 200 && xhr.status < 300 && xhr.responseText) {
      var data = JSON.parse(xhr.responseText);
      useTheme(data);
    } else {
      useTheme(defaults);
    }
  } catch (err) {
    useTheme(defaults);
  }
})();
