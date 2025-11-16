document.addEventListener('DOMContentLoaded', () => {
  const basePath = '../../src/Controllers';
  const url = new URL('../login.php', window.location.href);
  url.searchParams.set('context', 'admin');
  const path = window.location.pathname.split('/public/')[1] || 'admin/index.php';
  url.searchParams.set('redirect', path);

  function redirectToLogin() {
    window.location.href = url.toString();
  }

  function checkAuth() {
    return fetch(`${basePath}/AuthController.php?action=me`, { cache: 'no-store', credentials: 'same-origin' })
      .then((resp) => { if (!resp.ok) throw new Error('unauth'); return resp.json(); })
      .catch(redirectToLogin);
  }

  checkAuth();
});
