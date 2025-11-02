document.addEventListener('DOMContentLoaded', () => {
  const overlay = document.getElementById('adminOverlay');
  const loginForm = document.getElementById('adminLoginForm');
  const userEl = document.getElementById('adminUser');
  const passEl = document.getElementById('adminPass');
  const msgEl = document.getElementById('adminMsg');
  const logoutBtn = document.getElementById('btnAdminLogout');

  function showOverlay() { if (overlay) overlay.style.display = 'flex'; }
  function hideOverlay() { if (overlay) overlay.style.display = 'none'; }

  function checkAuth() {
    return fetch('../../api/auth/me.php', { cache: 'no-store', credentials: 'same-origin' })
      .then(r => { if (!r.ok) throw new Error('unauth'); return r.json(); })
      .then(() => { hideOverlay(); })
      .catch(() => { showOverlay(); });
  }

  loginForm?.addEventListener('submit', (e) => {
    e.preventDefault();
    msgEl.textContent = '';
    fetch('../../api/auth/login.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ username: userEl.value.trim(), password: passEl.value }) })
      .then(r=>r.json())
      .then(d=>{ if (d.status==='success') { hideOverlay(); } else { msgEl.textContent = d.message || 'Falha de login'; } })
      .catch(()=>{ msgEl.textContent = 'Erro de rede'; });
  });

  logoutBtn?.addEventListener('click', () => {
    fetch('../../api/auth/logout.php', { credentials: 'same-origin' }).finally(() => showOverlay());
  });

  checkAuth();
});

