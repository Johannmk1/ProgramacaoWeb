document.addEventListener('DOMContentLoaded', async () => {
  await App.loadTheme();
  setTimeout(() => { window.location.href = 'index.html'; }, 3500);
});

