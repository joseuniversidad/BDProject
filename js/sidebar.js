(function(){
      const btn = document.getElementById('toggleBtn');
      // Restaurar estado guardado
      const collapsed = localStorage.getItem('sidebar-collapsed') === 'true';
      if(collapsed) document.body.classList.add('collapsed');

      btn.addEventListener('click', () => {
        document.body.classList.toggle('collapsed');
        const isCollapsed = document.body.classList.contains('collapsed');
        localStorage.setItem('sidebar-collapsed', isCollapsed);
        console.log('sidebar collapsed?', isCollapsed);
      });

    })();