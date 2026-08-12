<?php
session_start();
if (!isset($_SESSION['autenticado'])) {
    header("Location: index.html");
    exit;
}

$seccion = $_GET['seccion'] ?? 'inicio';
$esAdmin = $_SESSION['rol'] === 1;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Usuarios</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body data-seccion="<?php echo $seccion; ?>">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Sistema Usuarios</h2>

        <a href="dashboard.php?seccion=inicio" class="<?php echo $seccion === 'inicio' ? 'active' : ''; ?>">
            Inicio
        </a>

        <?php if ($esAdmin): ?>
            <a href="dashboard.php?seccion=usuarios" class="<?php echo $seccion === 'usuarios' ? 'active' : ''; ?>">
                Usuarios
            </a>
            <a href="dashboard.php?seccion=tareas" class="<?php echo $seccion === 'tareas' ? 'active' : ''; ?>">
                Tareas
            </a>
        <?php else: ?>
            <a href="dashboard.php?seccion=mis_tareas" class="<?php echo $seccion === 'mis_tareas' ? 'active' : ''; ?>">
                Mis Tareas
            </a>
        <?php endif; ?>

        <a href="dashboard.php?seccion=perfil" class="<?php echo $seccion === 'perfil' ? 'active' : ''; ?>">
            Mi Perfil
        </a>

        <a href="../api/auth/logout.php" class="logout">Cerrar Sesion</a>
    </div>

    <!-- CONTENIDO -->
    <div class="content">

        <!-- MENSAJE -->
        <div id="mensaje"></div>

        <?php if ($seccion === 'inicio'): ?>
            <h1>Inicio</h1>
            <p>Bienvenido al sistema de usuarios, <?php echo $_SESSION['nombre'] . ' ' . $_SESSION['apellido']; ?>.</p>

            <?php if ($esAdmin): ?>
                <div class="cards">
                    <div class="card" id="card-usuarios">
                        <h3>-</h3>
                        <p>Usuarios</p>
                    </div>
                    <div class="card" id="card-tareas">
                        <h3>-</h3>
                        <p>Tareas</p>
                    </div>
                    <div class="card" id="card-pendientes">
                        <h3>-</h3>
                        <p>Tareas Pendientes</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="cards">
                    <div class="card" id="card-mis-tareas">
                        <h3>-</h3>
                        <p>Mis Tareas</p>
                    </div>
                    <div class="card" id="card-pendientes">
                        <h3>-</h3>
                        <p>Tareas Pendientes</p>
                    </div>
                </div>
            <?php endif; ?>

        <?php elseif ($seccion === 'usuarios' && $esAdmin): ?>
            <h1>Gestionar Usuarios</h1>
            <button class="btn btn-primary" onclick="mostrarFormCrear()">+ Nuevo Usuario</button>

            <!-- Formulario crear usuario (oculto) -->
            <div class="form-container" id="form-crear-usuario" style="display:none; margin: 20px 0;">
                <h2>Crear Usuario</h2>
                <div class="form-group">
                    <label>Primer Nombre</label>
                    <input type="text" id="crear-primer-nombre">
                </div>
                <div class="form-group">
                    <label>Segundo Nombre</label>
                    <input type="text" id="crear-segundo-nombre">
                </div>
                <div class="form-group">
                    <label>Primer Apellido</label>
                    <input type="text" id="crear-primer-apellido">
                </div>
                <div class="form-group">
                    <label>Segundo Apellido</label>
                    <input type="text" id="crear-segundo-apellido">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" id="crear-nombre-usuario">
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" id="crear-correo">
                </div>
                <div class="form-group">
                    <label>Contrasena</label>
                    <input type="password" id="crear-contrasena">
                </div>
                <div class="form-group">
                    <label>Rol</label>
                    <select id="crear-rol">
                        <option value="1">Administrador</option>
                        <option value="2" selected>Empleado</option>
                    </select>
                </div>
                <button class="btn btn-success" onclick="crearUsuario()">Crear</button>
                <button class="btn btn-danger" onclick="ocultarFormCrear()">Cancelar</button>
            </div>

            <!-- Formulario editar usuario (oculto) -->
            <div class="form-container" id="form-editar-usuario" style="display:none; margin: 20px 0;">
                <h2>Editar Usuario</h2>
                <input type="hidden" id="editar-id-usuario">
                <input type="hidden" id="editar-id-credencial">
                <div class="form-group">
                    <label>Primer Nombre</label>
                    <input type="text" id="editar-primer-nombre">
                </div>
                <div class="form-group">
                    <label>Segundo Nombre</label>
                    <input type="text" id="editar-segundo-nombre">
                </div>
                <div class="form-group">
                    <label>Primer Apellido</label>
                    <input type="text" id="editar-primer-apellido">
                </div>
                <div class="form-group">
                    <label>Segundo Apellido</label>
                    <input type="text" id="editar-segundo-apellido">
                </div>
                <div class="form-group">
                    <label>Nombre de Usuario</label>
                    <input type="text" id="editar-nombre-usuario">
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" id="editar-correo">
                </div>
                <button class="btn btn-success" onclick="guardarEdicionUsuario()">Guardar Cambios</button>
                <button class="btn btn-danger" onclick="ocultarFormEditar()">Cancelar</button>
            </div>

            <!-- Tabla de usuarios -->
            <table style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios">
                </tbody>
            </table>

        <?php elseif ($seccion === 'tareas' && $esAdmin): ?>
            <h1>Gestionar Tareas</h1>
            <button class="btn btn-primary" onclick="mostrarFormCrearTarea()">+ Nueva Tarea</button>

            <!-- Formulario crear tarea (oculto) -->
            <div class="form-container" id="form-crear-tarea" style="display:none; margin: 20px 0;">
                <h2>Crear Tarea</h2>
                <div class="form-group">
                    <label>Asignar a (Usuario)</label>
                    <select id="crear-tarea-usuario"></select>
                </div>
                <div class="form-group">
                    <label>Titulo</label>
                    <input type="text" id="crear-tarea-titulo">
                </div>
                <div class="form-group">
                    <label>Descripcion</label>
                    <textarea id="crear-tarea-descripcion"></textarea>
                </div>
                <div class="form-group">
                    <label>Fecha Fin</label>
                    <input type="date" id="crear-tarea-fecha">
                </div>
                <button class="btn btn-success" onclick="crearTarea()">Crear</button>
                <button class="btn btn-danger" onclick="ocultarFormCrearTarea()">Cancelar</button>
            </div>

            <!-- Formulario editar tarea (oculto) -->
            <div class="form-container" id="form-editar-tarea" style="display:none; margin: 20px 0;">
                <h2>Editar Tarea</h2>
                <input type="hidden" id="editar-id-tarea">
                <div class="form-group">
                    <label>Asignar a (Usuario)</label>
                    <select id="editar-tarea-usuario"></select>
                </div>
                <div class="form-group">
                    <label>Titulo</label>
                    <input type="text" id="editar-tarea-titulo">
                </div>
                <div class="form-group">
                    <label>Descripcion</label>
                    <textarea id="editar-tarea-descripcion"></textarea>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <select id="editar-tarea-estado">
                        <option value="pendiente">Pendiente</option>
                        <option value="en_progreso">En Progreso</option>
                        <option value="completada">Completada</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Fecha Fin</label>
                    <input type="date" id="editar-tarea-fecha">
                </div>
                <button class="btn btn-success" onclick="guardarEdicionTarea()">Guardar Cambios</button>
                <button class="btn btn-danger" onclick="ocultarFormEditarTarea()">Cancelar</button>
            </div>

            <table style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                        <th>Asignada a</th>
                        <th>Fecha Fin</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-tareas">
                </tbody>
            </table>

        <?php elseif ($seccion === 'mis_tareas'): ?>
            <h1>Mis Tareas</h1>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titulo</th>
                        <th>Descripcion</th>
                        <th>Estado</th>
                        <th>Fecha Creacion</th>
                        <th>Fecha Fin</th>
                        <th>Accion</th>
                    </tr>
                </thead>
                <tbody id="tabla-mis-tareas">
                </tbody>
            </table>

        <?php elseif ($seccion === 'perfil'): ?>
            <h1>Mi Perfil</h1>
            <div class="form-container">
                <div class="form-group">
                    <label>Primer Nombre</label>
                    <input type="text" id="perfil-primer-nombre">
                </div>
                <div class="form-group">
                    <label>Segundo Nombre</label>
                    <input type="text" id="perfil-segundo-nombre">
                </div>
                <div class="form-group">
                    <label>Primer Apellido</label>
                    <input type="text" id="perfil-primer-apellido">
                </div>
                <div class="form-group">
                    <label>Segundo Apellido</label>
                    <input type="text" id="perfil-segundo-apellido">
                </div>
                <div class="form-group">
                    <label>Correo</label>
                    <input type="email" id="perfil-correo">
                </div>
                <button class="btn btn-primary" onclick="editarPerfil()">Guardar Cambios</button>
            </div>

            <div class="form-container" style="margin-top: 20px;">
                <h2>Cambiar Contrasena</h2>
                <div class="form-group">
                    <label>Contrasena Actual</label>
                    <input type="password" id="perfil-contrasena-actual">
                </div>
                <div class="form-group">
                    <label>Nueva Contrasena</label>
                    <input type="password" id="perfil-contrasena-nueva">
                </div>
                <button class="btn btn-primary" onclick="cambiarContrasena()">Cambiar Contrasena</button>
            </div>

        <?php endif; ?>

    </div>

    <script>
        var esAdmin = <?php echo $esAdmin ? 'true' : 'false'; ?>;
        var userId = <?php echo $_SESSION['idUsuario']; ?>;
        var userRol = <?php echo $_SESSION['rol']; ?>;
    </script>
    <script src="js/dashboard.js"></script>
</body>
</html>
