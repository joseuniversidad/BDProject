(function () {
  const btn = document.getElementById('toggleBtn');
  const body = document.body;

  // Restaurar estado guardado
  if (localStorage.getItem('sidebar-collapsed') === 'true') {
    body.classList.add('collapsed');
  }

  btn.addEventListener('click', () => {
    body.classList.toggle('collapsed');
    const isCollapsed = body.classList.contains('collapsed');
    localStorage.setItem('sidebar-collapsed', isCollapsed);
  });
})();
