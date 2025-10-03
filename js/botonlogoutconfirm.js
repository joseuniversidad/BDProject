const btnLogout = document.getElementById("btnLogout");
btnLogout.addEventListener("click", function (e) {
  e.preventDefault();
  Swal.fire({
    title: "¿Deseas cerrar sesión?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#e74c3c",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Sí, cerrar sesión",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "../conexion_db/logout.php";
    }
  });
});
