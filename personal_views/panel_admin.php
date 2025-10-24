<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

include '../conexion_db/conexionOracle.php';

// ===================================================
// ✅ AGREGAR PROFESOR
// ===================================================
if (isset($_POST['agregar_profesor'])) {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $id_facultad = $_POST['id_facultad'];
    $email = $_POST['email'];
    $id_curso = $_POST['id_curso'];

    // Obtener nuevo ID_PROF
    $sqlId = "SELECT NVL(MAX(ID_PROF), 0) + 1 AS NUEVO_ID FROM PROFESORES";
    $stmtId = oci_parse($conn, $sqlId);
    oci_execute($stmtId);
    $row = oci_fetch_assoc($stmtId);
    $nuevoId = $row['NUEVO_ID'];

    // Insertar profesor
    $sqlInsert = "INSERT INTO PROFESORES (ID_PROF, NOMBRE, APELLIDOS, ID_FACULTAD, EMAIL, ESTADO)
                  VALUES (:id_prof, :nombre, :apellidos, :id_facultad, :email, 'S')";
    $stmtInsert = oci_parse($conn, $sqlInsert);
    oci_bind_by_name($stmtInsert, ":id_prof", $nuevoId);
    oci_bind_by_name($stmtInsert, ":nombre", $nombre);
    oci_bind_by_name($stmtInsert, ":apellidos", $apellidos);
    oci_bind_by_name($stmtInsert, ":id_facultad", $id_facultad);
    oci_bind_by_name($stmtInsert, ":email", $email);
    oci_execute($stmtInsert);

    // Asignar curso
    $sqlAsig = "INSERT INTO PROFESOR_CURSO (ID_PROF, ID_CURSO)
                VALUES (:id_prof, :id_curso)";
    $stmtAsig = oci_parse($conn, $sqlAsig);
    oci_bind_by_name($stmtAsig, ":id_prof", $nuevoId);
    oci_bind_by_name($stmtAsig, ":id_curso", $id_curso);
    oci_execute($stmtAsig);

    echo "<script>
            Swal.fire('✅ Éxito', 'Profesor agregado correctamente', 'success')
                 .then(() => location.reload());
          </script>";
}

// ===================================================
// ✏️ EDITAR PROFESOR
// ===================================================
if (isset($_POST['editar_profesor'])) {
    $id_prof = $_POST['id_prof'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $email = $_POST['email'];
    $id_facultad = $_POST['id_facultad'];

    $sqlUpdate = "UPDATE PROFESORES
                  SET NOMBRE = :nombre,
                      APELLIDOS = :apellidos,
                      EMAIL = :email,
                      ID_FACULTAD = :id_facultad
                  WHERE ID_PROF = :id_prof";
    $stmtUpdate = oci_parse($conn, $sqlUpdate);
    oci_bind_by_name($stmtUpdate, ":nombre", $nombre);
    oci_bind_by_name($stmtUpdate, ":apellidos", $apellidos);
    oci_bind_by_name($stmtUpdate, ":email", $email);
    oci_bind_by_name($stmtUpdate, ":id_facultad", $id_facultad);
    oci_bind_by_name($stmtUpdate, ":id_prof", $id_prof);
    oci_execute($stmtUpdate);

    echo "<script>
            Swal.fire('✏️ Actualizado', 'Datos del profesor actualizados', 'success')
                 .then(() => location.reload());
          </script>";
}

// ===================================================
// 🔁 ACTIVAR / DESACTIVAR PROFESOR
// ===================================================
if (isset($_POST['toggle_estado'])) {
    $id_prof = $_POST['id_prof'];
    $estadoActual = $_POST['toggle_estado'];
    $nuevoEstado = ($estadoActual === 'S') ? 'N' : 'S';

    $sqlEstado = "UPDATE PROFESORES SET ESTADO = :nuevoEstado WHERE ID_PROF = :id_prof";
    $stmtEstado = oci_parse($conn, $sqlEstado);
    oci_bind_by_name($stmtEstado, ":nuevoEstado", $nuevoEstado);
    oci_bind_by_name($stmtEstado, ":id_prof", $id_prof);
    oci_execute($stmtEstado);

    echo "<script>
            Swal.fire('Cambio de estado', 'El profesor ha sido actualizado', 'info')
                 .then(() => location.reload());
          </script>";
}

// ===================================================
// 📚 ASIGNAR CURSO A PROFESOR
// ===================================================
if (isset($_POST['asignar_curso'])) {
    $id_prof = $_POST['id_prof'];
    $id_curso = $_POST['id_curso'];

    // Verificar si ya está asignado
    $sqlCheck = "SELECT COUNT(*) AS EXISTE FROM PROFESOR_CURSO WHERE ID_PROF = :id_prof AND ID_CURSO = :id_curso";
    $stmtCheck = oci_parse($conn, $sqlCheck);
    oci_bind_by_name($stmtCheck, ":id_prof", $id_prof);
    oci_bind_by_name($stmtCheck, ":id_curso", $id_curso);
    oci_execute($stmtCheck);
    $row = oci_fetch_assoc($stmtCheck);

    if ($row['EXISTE'] > 0) {
        echo "<script>
                Swal.fire('⚠️ Aviso', 'Este curso ya está asignado a este profesor.', 'warning');
              </script>";
    } else {
        $sqlInsert = "INSERT INTO PROFESOR_CURSO (ID_PROF, ID_CURSO)
                      VALUES (:id_prof, :id_curso)";
        $stmtInsert = oci_parse($conn, $sqlInsert);
        oci_bind_by_name($stmtInsert, ":id_prof", $id_prof);
        oci_bind_by_name($stmtInsert, ":id_curso", $id_curso);
        oci_execute($stmtInsert);

        echo "<script>
                Swal.fire('✅ Éxito', 'Curso asignado correctamente.', 'success')
                     .then(() => location.reload());
              </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel del Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-light">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="#carreras" onclick="mostrarSeccion('carreras')">Carreras</a></li>
                    <li class="nav-item"><a class="nav-link" href="#agregar" onclick="mostrarSeccion('agregar')">Agregar Profesor</a></li>
                    <li class="nav-item"><a class="nav-link" href="#profesores" onclick="mostrarSeccion('profesores')">Profesores Registrados</a></li>
                    <li class="nav-item"><a class="nav-link btn btn-danger text-white ms-3" href="../conexion_db/logoutadmin.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">

        <h1 class="text-center mb-4">Bienvenido, <?php echo htmlspecialchars($_SESSION['admin_nombre']); ?></h1>

        <!-- CARRERAS -->
        <div id="carreras" class="seccion">
            <h3>Carreras / Facultades</h3>
            <div class="accordion" id="accordionCarreras">
                <?php
                $sql = "SELECT * FROM FACULTADES ORDER BY ID_FACULTAD";
                $stmt = oci_parse($conn, $sql);
                oci_execute($stmt);
                $idx = 0;
                while ($fac = oci_fetch_assoc($stmt)) {
                    $idx++;
                    $facId = $fac['ID_FACULTAD'];
                    $facNombre = htmlspecialchars($fac['NOMBRE']);
                    echo "<div class='accordion-item'>
                        <h2 class='accordion-header'>
                            <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#fac{$idx}'>
                                {$facNombre}
                            </button>
                        </h2>
                        <div id='fac{$idx}' class='accordion-collapse collapse'>
                            <div class='accordion-body'>";

                    $sqlCursos = "SELECT SEMESTRE, NOMBRE, CREDITOS FROM CURSOS WHERE ID_FACULTAD = :id_fac ORDER BY SEMESTRE, ID_CURSO";
                    $stmtCursos = oci_parse($conn, $sqlCursos);
                    oci_bind_by_name($stmtCursos, ":id_fac", $facId);
                    oci_execute($stmtCursos);
                    echo "<ul>";
                    while ($curso = oci_fetch_assoc($stmtCursos)) {
                        echo "<li>Sem {$curso['SEMESTRE']} - {$curso['NOMBRE']} ({$curso['CREDITOS']} créditos)</li>";
                    }
                    echo "</ul></div></div></div>";
                }
                ?>
            </div>
        </div>

        <!-- AGREGAR PROFESOR -->
        <div id="agregar" class="seccion" style="display:none;">
            <div class="p-5 mb-4 bg-light rounded-3 shadow-sm">
                <div class="container-fluid py-4">
                    <h3 class="display-6 fw-bold">Agregar Profesor</h3>
                    <form action="" method="POST" class="mt-4">
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" placeholder="Nombre" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Apellidos</label>
                            <input type="text" name="apellidos" class="form-control" placeholder="Apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ID Facultad</label>
                            <input type="number" name="id_facultad" class="form-control" placeholder="ID Facultad" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" class="form-control" placeholder="Correo" required>
                        </div>
                        <div class="col-md-12">
                            <label for="curso" class="form-label fw-bold">Asignar Curso</label>
                            <select name="id_curso" id="curso" class="form-select" required>
                                <option value="">Seleccione un curso...</option>
                                <?php
                                // Obtener todas las facultades
                                $sqlFac = "SELECT ID_FACULTAD, NOMBRE FROM FACULTADES ORDER BY NOMBRE";
                                $stmtFac = oci_parse($conn, $sqlFac);
                                oci_execute($stmtFac);

                                while ($fac = oci_fetch_assoc($stmtFac)) {
                                    $id_fac = $fac['ID_FACULTAD'];
                                    $nombre_fac = htmlspecialchars($fac['NOMBRE']);

                                    echo "<optgroup label='{$nombre_fac}'>";

                                    // Obtener cursos de la facultad ordenados por ID_CURSO ascendente
                                    $sqlCursos = "SELECT ID_CURSO, NOMBRE 
                          FROM CURSOS 
                          WHERE ID_FACULTAD = :id_fac 
                          ORDER BY ID_CURSO ASC";
                                    $stmtCursos = oci_parse($conn, $sqlCursos);
                                    oci_bind_by_name($stmtCursos, ":id_fac", $id_fac);
                                    oci_execute($stmtCursos);

                                    while ($curso = oci_fetch_assoc($stmtCursos)) {
                                        $id_curso = $curso['ID_CURSO'];
                                        $nombre_curso = htmlspecialchars($curso['NOMBRE']);
                                        echo "<option value='{$id_curso}'>{$id_curso} - {$nombre_curso}</option>";
                                    }

                                    echo "</optgroup>";
                                }
                                ?>
                            </select>
                        </div>

                    </form>
                </div>
                <div><button type="submit" name="agregar_profesor" class="btn btn-success btn-lg">Registrar Profesor</button></div>
            </div>
        </div>

        <!-- PROFESORES REGISTRADOS -->
        <div id="profesores" class="seccion" style="display:none;">
            <h3>Profesores Registrados</h3>
            <table class="table table-bordered table-hover bg-white shadow-sm rounded">
                <thead class="table-success">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Facultad</th>
                        <th>Email</th>
                        <th>Cursos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sqlProf = "SELECT P.ID_PROF, P.NOMBRE, P.APELLIDOS, P.ID_FACULTAD, F.NOMBRE AS FACULTAD, P.EMAIL, P.ESTADO
                     FROM PROFESORES P
            JOIN FACULTADES F ON P.ID_FACULTAD = F.ID_FACULTAD
            ORDER BY P.ID_PROF";

                    $stmtProf = oci_parse($conn, $sqlProf);
                    oci_execute($stmtProf);
                    $contador = 1;
                    while ($prof = oci_fetch_assoc($stmtProf)) {
                        $id_prof = $prof['ID_PROF'];
                        $estadoBtn = $prof['ESTADO'] === 'S' ? 'Desactivar' : 'Activar';
                        $estadoValor = $prof['ESTADO'];
                        $sqlCursos = "SELECT C.NOMBRE FROM CURSOS C 
                          JOIN PROFESOR_CURSO PC ON C.ID_CURSO = PC.ID_CURSO
                          WHERE PC.ID_PROF = :id_prof ORDER BY C.ID_CURSO";
                        $stmtCursos = oci_parse($conn, $sqlCursos);
                        oci_bind_by_name($stmtCursos, ":id_prof", $id_prof);
                        oci_execute($stmtCursos);
                        $cursos_list = [];
                        while ($c = oci_fetch_assoc($stmtCursos)) $cursos_list[] = $c['NOMBRE'];
                        $cursos_str = implode(", ", $cursos_list);

                        echo "<tr>
                    <td>{$contador}</td>
                    <td>{$prof['NOMBRE']} {$prof['APELLIDOS']}</td>
                    <td>{$prof['FACULTAD']}</td>
                    <td>{$prof['EMAIL']}</td>
                    <td>{$cursos_str}</td>
                    <td>
                        <!-- Activar/Desactivar -->
                        <form style='display:inline;' method='POST'>
                            <input type='hidden' name='id_prof' value='{$id_prof}'>
                            <input type='hidden' name='toggle_estado' value='{$estadoValor}'>
                            <button type='submit' class='btn btn-sm btn-warning'>{$estadoBtn}</button>
                        </form>

                        <!-- Editar -->
                        <button class='btn btn-sm btn-primary' data-bs-toggle='modal' data-bs-target='#editar{$id_prof}'>Editar</button>

                        <!-- Agregar Curso -->
                        <button class='btn btn-sm btn-success' data-bs-toggle='modal' data-bs-target='#curso{$id_prof}'>Agregar Curso</button>
                    </td>
                  </tr>";

                        // Modal Editar
                        echo "
            <div class='modal fade' id='editar{$id_prof}' tabindex='-1'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <form method='POST'>
                    <div class='modal-header'>
                      <h5 class='modal-title'>Editar Profesor</h5>
                      <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                    </div>
                    <div class='modal-body'>
                      <input type='hidden' name='id_prof' value='{$id_prof}'>
                      <div class='mb-3'><label>Nombre</label><input type='text' name='nombre' class='form-control' value='{$prof['NOMBRE']}' required></div>
                      <div class='mb-3'><label>Apellidos</label><input type='text' name='apellidos' class='form-control' value='{$prof['APELLIDOS']}' required></div>
                      <div class='mb-3'><label>Email</label><input type='email' name='email' class='form-control' value='{$prof['EMAIL']}' required></div>
                      <div class='mb-3'><label>ID Facultad</label><input type='number' name='id_facultad' class='form-control' value='{$prof['ID_FACULTAD']}' required></div>
                    </div>
                    <div class='modal-footer'>
                      <button type='submit' name='editar_profesor' class='btn btn-primary'>Guardar</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>";

                        // Modal Asignar Curso
                        echo "
<div class='modal fade' id='curso{$id_prof}' tabindex='-1'>
  <div class='modal-dialog'>
    <div class='modal-content'>
      <form method='POST'>
        <div class='modal-header'>
          <h5 class='modal-title'>Asignar Curso</h5>
          <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
        </div>
        <div class='modal-body'>
          <input type='hidden' name='id_prof' value='{$id_prof}'>
          <div class='mb-3'>
            <label class='form-label fw-bold'>Seleccione un curso</label>
            <select name='id_curso' class='form-select' required>
              <option value=''>Seleccione un curso...</option>";

                        // 🔹 Obtener todas las facultades
                        $sqlFacs = "SELECT ID_FACULTAD, NOMBRE FROM FACULTADES ORDER BY NOMBRE ASC";
                        $stmtFacs = oci_parse($conn, $sqlFacs);
                        oci_execute($stmtFacs);

                        while ($fac = oci_fetch_assoc($stmtFacs)) {
                            $id_fac = $fac['ID_FACULTAD'];
                            $nombre_fac = htmlspecialchars($fac['NOMBRE']);

                            echo "<optgroup label='{$nombre_fac}'>";

                            // 🔹 Obtener cursos por facultad y ordenarlos por número si es posible
                            $sqlCursos2 = "
                    SELECT ID_CURSO, COD_CURSO, NOMBRE 
                    FROM CURSOS 
                    WHERE ID_FACULTAD = :id_fac
                    ORDER BY 
                      CASE 
                        WHEN REGEXP_LIKE(COD_CURSO, '^[0-9]+$') THEN TO_NUMBER(COD_CURSO)
                        ELSE NULL
                      END ASC,
                      COD_CURSO ASC";

                            $stmtCursos2 = oci_parse($conn, $sqlCursos2);
                            oci_bind_by_name($stmtCursos2, ":id_fac", $id_fac);
                            oci_execute($stmtCursos2);

                            $hayCursos = false;
                            while ($curso = oci_fetch_assoc($stmtCursos2)) {
                                $hayCursos = true;
                                $cod = htmlspecialchars($curso['COD_CURSO']);
                                $nombre_curso = htmlspecialchars($curso['NOMBRE']);
                                $id_curso = $curso['ID_CURSO'];
                                echo "<option value='{$id_curso}'>[{$cod}] - {$nombre_curso}</option>";
                            }

                            if (!$hayCursos) {
                                echo "<option disabled>(Sin cursos disponibles)</option>";
                            }

                            echo "</optgroup>";
                        }

                        echo "      </select>
          </div>
        </div>
        <div class='modal-footer'>
          <button type='submit' name='asignar_curso' class='btn btn-success'>Asignar</button>
        </div>
      </form>
    </div>
  </div>
</div>";


                        $contador++;
                    }
                    ?>
                </tbody>
            </table>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Función para mostrar solo una sección
            function mostrarSeccion(id) {
                document.querySelectorAll('.seccion').forEach(s => s.style.display = 'none');
                document.getElementById(id).style.display = 'block';
            }
        </script>
</body>

</html>
<?php oci_close($conn); ?>