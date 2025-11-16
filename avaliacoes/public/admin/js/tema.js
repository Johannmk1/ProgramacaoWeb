const apiTheme = '../../src/Controllers/AdminController.php?resource=theme';
const withCreds = (options = {}) => ({ credentials: 'same-origin', ...options });

const formFields = {
  primaryColor: document.getElementById('primaryColor'),
  secondaryColor: document.getElementById('secondaryColor'),
  tertiaryColor: document.getElementById('tertiaryColor'),
  cardMaxWidth: document.getElementById('cardMaxWidth'),
};
const msg = document.getElementById('msg');
const preview = document.getElementById('themePreview');

const defaults = {
  primaryColor: '#2563eb',
  secondaryColor: '#0ea5e9',
  tertiaryColor: '#f7f8fa',
  cardMaxWidth: '820px',
};

function flashTheme(text, ok = true) {
  msg.textContent = text;
  msg.style.color = ok ? '#065f46' : '#b91c1c';
  setTimeout(() => (msg.textContent = ''), 2500);
}

function normalizeHex(hex, fallback) {
  if (!hex) return fallback;
  let c = hex.replace('#', '');
  if (c.length === 3) c = c[0] + c[0] + c[1] + c[1] + c[2] + c[2];
  return c.length === 6 ? `#${c}` : fallback;
}

function contrastColor(hex) {
  const normalized = normalizeHex(hex, '#000000');
  const r = parseInt(normalized.substring(1, 3), 16) / 255;
  const g = parseInt(normalized.substring(3, 5), 16) / 255;
  const b = parseInt(normalized.substring(5, 7), 16) / 255;
  const luma = 0.2126 * r + 0.7152 * g + 0.0722 * b;
  return luma > 0.5 ? '#0f172a' : '#ffffff';
}

function mixColor(hexA, hexB, amount = 0.5) {
  const a = normalizeHex(hexA, '#ffffff').substring(1);
  const b = normalizeHex(hexB, '#ffffff').substring(1);
  const w = Math.min(Math.max(amount, 0), 1);
  const toRgb = (hex) => [parseInt(hex.substring(0, 2), 16), parseInt(hex.substring(2, 4), 16), parseInt(hex.substring(4, 6), 16)];
  const [rA, gA, bA] = toRgb(a);
  const [rB, gB, bB] = toRgb(b);
  const mix = (x, y) => Math.round(x * w + y * (1 - w));
  const toHex = (v) => v.toString(16).padStart(2, '0');
  return `#${toHex(mix(rA, rB))}${toHex(mix(gA, gB))}${toHex(mix(bA, bB))}`;
}

function applyPreview(theme) {
  if (!preview) return;
  const primary = theme.primaryColor;
  const secondary = theme.secondaryColor;
  const tertiary = theme.tertiaryColor;
  const primaryContrast = contrastColor(primary);
  const secondaryContrast = contrastColor(secondary);
  const tertiaryContrast = contrastColor(tertiary);
  const feedbackBg = mixColor(secondary, '#ffffff', 0.18);
  const feedbackBorder = mixColor(secondary, '#ffffff', 0.55);
  const feedbackPlaceholder = mixColor(secondary, '#111827', 0.65);
  const feedbackText = contrastColor(feedbackBg);
  const previewCard = preview.querySelector('.preview-card');
  const targets = [preview, previewCard].filter(Boolean);
  const set = (prop, value, scopedTargets = targets) => {
    scopedTargets.forEach((el) => el?.style.setProperty(prop, value));
  };

  set('--t-primary', primary, [preview]);
  set('--t-secondary', secondary, [preview]);
  set('--t-bg', tertiary, [preview]);
  set('--t-text', tertiaryContrast, [preview]);
  set('--k-card-max', theme.cardMaxWidth, [preview]);

  set('--k-bg', tertiary, [previewCard]);
  set('--k-card', tertiary, [previewCard]);
  set('--k-text', tertiaryContrast, [previewCard]);
  set('--k-primary', primary, [previewCard]);
  set('--k-title', tertiaryContrast, [previewCard]);
  set('--k-sub', '#475569', [previewCard]);
  set('--k-muted', '#64748b', [previewCard]);

  set('--color-primary', primary);
  set('--color-primary-contrast', primaryContrast);
  set('--color-secondary', secondary);
  set('--color-secondary-contrast', secondaryContrast);
  set('--color-tertiary', tertiary);
  set('--color-tertiary-contrast', tertiaryContrast);
  set('--color-bg', tertiary);
  set('--color-text', tertiaryContrast);
  set('--color-title', tertiaryContrast);

  set('--feedback-bg', feedbackBg);
  set('--feedback-border', feedbackBorder);
  set('--feedback-placeholder', feedbackPlaceholder);
  set('--feedback-text', feedbackText);

  set('--nps-btn-gradient-start', '#ef4444', [previewCard]);
  set('--nps-btn-gradient-mid', '#f59e0b', [previewCard]);
  set('--nps-btn-gradient-end', '#22c55e', [previewCard]);
}

function fillForm(theme) {
  Object.keys(formFields).forEach((key) => {
    if (formFields[key]) {
      formFields[key].value = key.includes('Color') ? normalizeHex(theme[key], defaults[key]) : (theme[key] || defaults[key]);
    }
  });
  applyPreview(theme);
}

function getThemeValues() {
  return {
    primaryColor: normalizeHex(formFields.primaryColor?.value, defaults.primaryColor),
    secondaryColor: normalizeHex(formFields.secondaryColor?.value, defaults.secondaryColor),
    tertiaryColor: normalizeHex(formFields.tertiaryColor?.value, defaults.tertiaryColor),
    cardMaxWidth: (formFields.cardMaxWidth?.value || defaults.cardMaxWidth).trim(),
  };
}

function bindLivePreview() {
  Object.values(formFields).forEach((input) => {
    input?.addEventListener('input', () => applyPreview(getThemeValues()));
  });
}

function loadTheme() {
  fetch(apiTheme, withCreds({ cache: 'no-store' }))
    .then((r) => r.json())
    .then((theme) => fillForm({ ...defaults, ...theme }))
    .catch(() => flashTheme('Erro ao carregar tema', false));
}

document.getElementById('btnSalvar').addEventListener('click', () => {
  const body = getThemeValues();
  fetch(apiTheme, withCreds({
    method: 'PUT',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  }))
    .then((r) => r.json())
    .then((d) => {
      if (d.status === 'success') {
        flashTheme('Tema salvo');
        fillForm(d.theme || body);
      } else {
        flashTheme(d.message || 'Erro ao salvar tema', false);
      }
    })
    .catch(() => flashTheme('Erro ao salvar tema', false));
});

bindLivePreview();
loadTheme();
