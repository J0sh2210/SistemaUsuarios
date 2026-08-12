// ===== FUNCIONES GENERALES =====
function mostrarMensaje(texto, tipo) {
    const div = document.getElementById('mensaje');
    div.textContent = texto;
    div.className = tipo === 'exito' ? 'mensaje-exito' : 'mensaje-error';
    setTimeout(() => { div.className = ''; }, 3000);
}

function apiGet(url) {
    return fetch(url, { credentials: 'include' }).then(r => r.json());
}

function apiPost(url, data) {
    return fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
        credentials: 'include'
    }).then(r => r.json());
}

// ===== CARGAR DATOS AL INICIO =====
document.addEventListener('DOMContentLoaded', function() {
    const seccion = document.body.getAttribute('data-seccion');

    if (seccion === 'inicio') {
        cargarEstadisticas();
    } else if (seccion === 'usuarios') {
        cargarUsuarios();
    } else if (seccion === 'tareas') {
        cargarTareas();
        cargarSelectUsuarios();
    } else if (seccion === 'mis_tareas') {
        cargarMisTareas();
    } else if (seccion === 'perfil') {
        cargarPerfil();
    }
});

// ===== INICIO - ESTADISTICAS =====
function cargarEstadisticas() {
    if (esAdmin) {
        apiGet('../api/usuarios/listarUsuarios.php').then(data => {
            document.querySelector('#card-usuarios h3').textContent = data.length;
        });
        apiGet('../api/tareas/listar.php').then(data => {
            document.querySelector('#card-tareas h3').textContent = data.length;
            const pendientes = data.filter(t => t.Estado === 'pendiente').length;
            document.querySelector('#card-pendientes h3').textContent = pendientes;
        });
    } else {
        apiGet('../api/tareas/listar.php').then(data => {
            document.querySelector('#card-mis-tareas h3').textContent = data.length;
            const pendientes = data.filter(t => t.Estado === 'pendiente').length;
            document.querySelector('#card-pendientes h3').textContent = pendientes;
        });
    }
}

// ===== USUARIOS =====
function cargarUsuarios() {
    apiGet('../api/usuarios/listarUsuarios.php').then(data => {
        const tbody = document.getElementById('tabla-usuarios');
        tbody.innerHTML = '';
        data.forEach(u => {
            tbody.innerHTML += `
                <tr>
                    <td>${u.IdUsuario}</td>
                    <td>${u.PrimerNombre} ${u.SegundoNombre || ''}</td>
                    <td>${u.PrimerApellido} ${u.SegundoApellido || ''}</td>
                    <td>${u.NombreUsuario}</td>
                    <td>${u.Correo}</td>
                    <td><span class="badge ${u.Rol === 'administrador' ? 'badge-admin' : 'badge-empleado'}">${u.Rol}</span></td>
                    <td class="acciones">
                        <button class="btn btn-editar" onclick="editarUsuario(${u.IdUsuario})">Editar</button>
                        <button class="btn btn-eliminar" onclick="eliminarUsuario(${u.IdUsuario}, ${u.IdCredencial})">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    });
}

function mostrarFormCrear() {
    document.getElementById('form-crear-usuario').style.display = 'block';
}

function ocultarFormCrear() {
    document.getElementById('form-crear-usuario').style.display = 'none';
}

function crearUsuario() {
    const datos = {
        PrimerNombre: document.getElementById('crear-primer-nombre').value,
        SegundoNombre: document.getElementById('crear-segundo-nombre').value,
        PrimerApellido: document.getElementById('crear-primer-apellido').value,
        SegundoApellido: document.getElementById('crear-segundo-apellido').value,
        NombreUsuario: document.getElementById('crear-nombre-usuario').value,
        Correo: document.getElementById('crear-correo').value,
        contrasena: document.getElementById('crear-contrasena').value,
        IdRol: parseInt(document.getElementById('crear-rol').value)
    };

    apiPost('../api/usuarios/crear.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) {
            cargarUsuarios();
            ocultarFormCrear();
        }
    });
}

function eliminarUsuario(idUsuario, idCredencial) {
    if (!confirm('Estas seguro de eliminar este usuario?')) return;

    apiPost('../api/usuarios/eliminar.php', {
        IdUsuario: idUsuario,
        IdCredencial: idCredencial
    }).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) cargarUsuarios();
    });
}

// ===== EDITAR USUARIO =====
function editarUsuario(id) {
    apiPost('../api/usuarios/obtenerUsuario.php', { IdUsuario: id }).then(data => {
        if (data.success) {
            const u = data.usuario;
            document.getElementById('editar-id-usuario').value = u.IdUsuario;
            document.getElementById('editar-id-credencial').value = u.IdCredencial;
            document.getElementById('editar-primer-nombre').value = u.PrimerNombre || '';
            document.getElementById('editar-segundo-nombre').value = u.SegundoNombre || '';
            document.getElementById('editar-primer-apellido').value = u.PrimerApellido || '';
            document.getElementById('editar-segundo-apellido').value = u.SegundoApellido || '';
            document.getElementById('editar-nombre-usuario').value = u.NombreUsuario || '';
            document.getElementById('editar-correo').value = u.Correo || '';
            document.getElementById('form-editar-usuario').style.display = 'block';
            document.getElementById('form-crear-usuario').style.display = 'none';
        }
    });
}

function ocultarFormEditar() {
    document.getElementById('form-editar-usuario').style.display = 'none';
}

function guardarEdicionUsuario() {
    const datos = {
        IdUsuario: parseInt(document.getElementById('editar-id-usuario').value),
        IdCredencial: parseInt(document.getElementById('editar-id-credencial').value),
        PrimerNombre: document.getElementById('editar-primer-nombre').value,
        SegundoNombre: document.getElementById('editar-segundo-nombre').value,
        PrimerApellido: document.getElementById('editar-primer-apellido').value,
        SegundoApellido: document.getElementById('editar-segundo-apellido').value,
        NombreUsuario: document.getElementById('editar-nombre-usuario').value,
        Correo: document.getElementById('editar-correo').value
    };

    apiPost('../api/usuarios/editar.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) {
            cargarUsuarios();
            ocultarFormEditar();
        }
    });
}

// ===== TAREAS =====
function cargarTareas() {
    apiGet('../api/tareas/listar.php').then(data => {
        const tbody = document.getElementById('tabla-tareas');
        tbody.innerHTML = '';
        data.forEach(t => {
            tbody.innerHTML += `
                <tr>
                    <td>${t.IdTarea}</td>
                    <td>${t.Titulo}</td>
                    <td>${t.Descripcion || ''}</td>
                    <td><span class="badge badge-${t.Estado}">${t.Estado}</span></td>
                    <td>${t.Usuario || 'Sin asignar'}</td>
                    <td>${t.FechaFin || '-'}</td>
                    <td class="acciones">
                        <button class="btn btn-editar" onclick="editarTarea(${t.IdTarea})">Editar</button>
                        <button class="btn btn-eliminar" onclick="eliminarTarea(${t.IdTarea})">Eliminar</button>
                    </td>
                </tr>
            `;
        });
    });
}

function cargarSelectUsuarios() {
    apiGet('../api/usuarios/listarUsuarios.php').then(data => {
        const select = document.getElementById('crear-tarea-usuario');
        select.innerHTML = '';
        data.forEach(u => {
            select.innerHTML += `<option value="${u.IdUsuario}">${u.PrimerNombre} ${u.PrimerApellido}</option>`;
        });
    });
}

function mostrarFormCrearTarea() {
    document.getElementById('form-crear-tarea').style.display = 'block';
}

function ocultarFormCrearTarea() {
    document.getElementById('form-crear-tarea').style.display = 'none';
}

function crearTarea() {
    const datos = {
        IdUsuario: parseInt(document.getElementById('crear-tarea-usuario').value),
        Titulo: document.getElementById('crear-tarea-titulo').value,
        Descripcion: document.getElementById('crear-tarea-descripcion').value,
        FechaFin: document.getElementById('crear-tarea-fecha').value || null
    };

    apiPost('../api/tareas/crear.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) {
            cargarTareas();
            ocultarFormCrearTarea();
        }
    });
}

function eliminarTarea(idTarea) {
    if (!confirm('Estas seguro de eliminar esta tarea?')) return;

    apiPost('../api/tareas/eliminar.php', { IdTarea: idTarea }).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) cargarTareas();
    });
}

// ===== EDITAR TAREA =====
function editarTarea(id) {
    apiPost('../api/tareas/obtenerTarea.php', { IdTarea: id }).then(data => {
        if (data.success) {
            const t = data.tarea;
            document.getElementById('editar-id-tarea').value = t.IdTarea;
            document.getElementById('editar-tarea-titulo').value = t.Titulo || '';
            document.getElementById('editar-tarea-descripcion').value = t.Descripcion || '';
            document.getElementById('editar-tarea-estado').value = t.Estado || 'pendiente';
            document.getElementById('editar-tarea-fecha').value = t.FechaFin || '';

            // Cargar select de usuarios y seleccionar el actual
            apiGet('../api/usuarios/listarUsuarios.php').then(usuarios => {
                const select = document.getElementById('editar-tarea-usuario');
                select.innerHTML = '';
                usuarios.forEach(u => {
                    const selected = u.IdUsuario == t.IdUsuario ? 'selected' : '';
                    select.innerHTML += `<option value="${u.IdUsuario}" ${selected}>${u.PrimerNombre} ${u.PrimerApellido}</option>`;
                });
            });

            document.getElementById('form-editar-tarea').style.display = 'block';
            document.getElementById('form-crear-tarea').style.display = 'none';
        }
    });
}

function ocultarFormEditarTarea() {
    document.getElementById('form-editar-tarea').style.display = 'none';
}

function guardarEdicionTarea() {
    const datos = {
        IdTarea: parseInt(document.getElementById('editar-id-tarea').value),
        IdUsuario: parseInt(document.getElementById('editar-tarea-usuario').value),
        Titulo: document.getElementById('editar-tarea-titulo').value,
        Descripcion: document.getElementById('editar-tarea-descripcion').value,
        Estado: document.getElementById('editar-tarea-estado').value,
        FechaFin: document.getElementById('editar-tarea-fecha').value || null
    };

    apiPost('../api/tareas/editar.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) {
            cargarTareas();
            ocultarFormEditarTarea();
        }
    });
}

// ===== MIS TAREAS =====
function cargarMisTareas() {
    apiGet('../api/tareas/listar.php').then(data => {
        const tbody = document.getElementById('tabla-mis-tareas');
        tbody.innerHTML = '';
        data.forEach(t => {
            tbody.innerHTML += `
                <tr>
                    <td>${t.IdTarea}</td>
                    <td>${t.Titulo}</td>
                    <td>${t.Descripcion || ''}</td>
                    <td><span class="badge badge-${t.Estado}">${t.Estado}</span></td>
                    <td>${t.FechaCreacion}</td>
                    <td>${t.FechaFin || '-'}</td>
                    <td>
                        <select onchange="cambiarEstado(${t.IdTarea}, this.value)">
                            <option value="pendiente" ${t.Estado === 'pendiente' ? 'selected' : ''}>Pendiente</option>
                            <option value="en_progreso" ${t.Estado === 'en_progreso' ? 'selected' : ''}>En Progreso</option>
                            <option value="completada" ${t.Estado === 'completada' ? 'selected' : ''}>Completada</option>
                        </select>
                    </td>
                </tr>
            `;
        });
    });
}

function cambiarEstado(idTarea, nuevoEstado) {
    apiPost('../api/tareas/editar.php', {
        IdTarea: idTarea,
        Estado: nuevoEstado
    }).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
    });
}

// ===== PERFIL =====
function cargarPerfil() {
    apiPost('../api/usuarios/obtenerUsuario.php', {}).then(data => {
        if (data.success) {
            const u = data.usuario;
            document.getElementById('perfil-primer-nombre').value = u.PrimerNombre || '';
            document.getElementById('perfil-segundo-nombre').value = u.SegundoNombre || '';
            document.getElementById('perfil-primer-apellido').value = u.PrimerApellido || '';
            document.getElementById('perfil-segundo-apellido').value = u.SegundoApellido || '';
            document.getElementById('perfil-correo').value = u.Correo || '';
        }
    });
}

function editarPerfil() {
    const datos = {
        PrimerNombre: document.getElementById('perfil-primer-nombre').value,
        SegundoNombre: document.getElementById('perfil-segundo-nombre').value,
        PrimerApellido: document.getElementById('perfil-primer-apellido').value,
        SegundoApellido: document.getElementById('perfil-segundo-apellido').value,
        Correo: document.getElementById('perfil-correo').value
    };

    apiPost('../api/usuarios/editar.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
    });
}

function cambiarContrasena() {
    const datos = {
        Contrasena_Actual: document.getElementById('perfil-contrasena-actual').value,
        Contrasena_Nueva: document.getElementById('perfil-contrasena-nueva').value
    };

    apiPost('../api/usuarios/cambiar_contrasena.php', datos).then(data => {
        mostrarMensaje(data.message, data.success ? 'exito' : 'error');
        if (data.success) {
            document.getElementById('perfil-contrasena-actual').value = '';
            document.getElementById('perfil-contrasena-nueva').value = '';
        }
    });
}
