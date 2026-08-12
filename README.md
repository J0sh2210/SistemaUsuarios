# Sistema de Usuarios - Documentacion de la API

API REST en PHP para gestion de usuarios con autenticacion, registro, manejo de sesiones y sistema de roles.

---

## Tecnologias

- PHP (PDO para conexion a base de datos)
- MySQL
- PHPMailer (envio de correos)
- phpdotenv (variables de entorno)
- Composer (gestor de dependencias)

---

## Estructura del Proyecto

```
SistemaUsuarios/
├── api/
│   ├── auth/
│   │   ├── login.php                 # Login con correo y contrasena
│   │   ├── logout.php                # Cierre de sesion (redirige al login)
│   │   ├── solicitar_token.php       # Solicitar token por correo
│   │   └── verificar_token_email.php # Verificar token y crear sesion
│   ├── usuarios/
│   │   ├── cambiar_contrasena.php    # Cambiar contrasena del usuario
│   │   ├── crear.php                 # Crear usuario (admin)
│   │   ├── editar.php                # Editar usuario (admin/usuario)
│   │   ├── eliminar.php              # Eliminar usuario (admin)
│   │   ├── listarUsuarios.php        # Listar usuarios (admin)
│   │   └── obtenerUsuario.php        # Obtener datos de usuario
│   └── tareas/
│       ├── crear.php                 # Crear tarea (admin)
│       ├── editar.php                # Editar tarea (admin/usuario)
│       ├── eliminar.php              # Eliminar tarea (admin)
│       ├── listar.php                # Listar tareas (admin/usuario)
│       └── obtenerTarea.php          # Obtener datos de tarea
├── config/
│   ├── conexion.php                  # Conexion a BD (PDO, carga .env)
│   └── correo.php                    # Configuracion SMTP para correos
├── middleware/
│   ├── auth.php                      # Verifica sesion activa
│   └── rol_admin.php                 # Verifica rol de administrador
├── public/
│   ├── index.html                    # Pagina de login
│   ├── dashboard.php                 # Panel principal
│   ├── css/
│   │   ├── style.css                 # Estilos del login
│   │   └── dashboard.css             # Estilos del dashboard
│   └── js/
│       ├── auth.js                   # Logica del login
│       └── dashboard.js              # Logica del dashboard (CRUD)
├── services/
│   ├── mail_service.php              # Servicio de envio de correos
│   └── token_service.php             # Servicio de gestion de tokens
├── sql/
│   └── creardb.sql                   # Script de creacion de base de datos
├── vendor/                           # Dependencias de Composer
├── .env                              # Variables de entorno (no commitear)
├── .gitignore                        # Archivos ignorados por git
├── composer.json                     # Configuracion de Composer
└── README.md                         # Este archivo
```

---

## Base de Datos

El esquema se encuentra en `sql/creardb.sql` y consta de las siguientes tablas:

| Tabla        | Descripcion |
|--------------|-------------|
| `Rol`        | Almacena los roles del sistema (administrador, empleado) |
| `Credencial` | Guarda credenciales de acceso (usuario, correo, contrasena hasheada) |
| `LoginToken` | Almacena tokens de sesion con fecha de expiracion |
| `Usuario`    | Informacion personal del usuario, relacionada con su credencial y rol |
| `Tarea`      | Tareas asignadas a usuarios (creadas por admins) |

### Relaciones:
- `Usuario.IdRol` → `Rol.IdRol`
- `Usuario.IdCredencial` → `Credencial.IdCredencial`
- `LoginToken.IdCredencial` → `Credencial.IdCredencial`
- `Tarea.IdCreador` → `Usuario.IdUsuario`
- `Tarea.IdUsuario` → `Usuario.IdUsuario`

---

## Configuracion

### .env
Variables de entorno cargadas por `conexion.php` y `correo.php`:

```
DB_HOST=localhost
DB_NAME=sistemausuario
DB_USER=root
DB_PASSWORD=

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_correo@gmail.com
MAIL_PASSWORD=tu_password_app
MAIL_FROM_NAME="Sistema de Usuarios"

APP_URL=http://localhost/SistemaUsuarios
```

### config/conexion.php
Establece la conexion a la base de datos MySQL usando PDO. Carga las credenciales desde `.env`.

- **Charset:** utf8
- **Modo de error:** ERRMODE_EXCEPTION
- ** Conexion persistente:** PDO::ATTR_PERSISTENT => true

### config/correo.php
Retorna la configuracion SMTP para el envio de correos, usando las variables de entorno cargadas por `conexion.php`.

**Variables utilizadas:**
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_NAME`

---

## Servicios (services/)

### services/token_service.php

Clase `TokenService` para la gestion centralizada de tokens de sesion.

**Metodo: crearToken(int $idCredencial): string**
1. Inicia una transaccion
2. Marca como usados (`Usado = TRUE`) todos los tokens activos del usuario
3. Genera un nuevo token aleatorio de 64 caracteres hexadecimales
4. Establece expiracion a 15 minutos
5. Inserta el nuevo token en `LoginToken`
6. Confirma la transaccion o hace rollback en caso de error
7. Retorna el token generado

---

### services/mail_service.php

Clase `mail_service` para el envio de correos electronicos usando PHPMailer.

**Metodo: enviarLoginLink(string $correo, string $token): bool**
1. Configura PHPMailer con SMTP (STARTTLS)
2. Establece el remitente desde las variables de entorno
3. Genera un enlace de inicio de sesion usando `$_ENV["APP_URL"]`
4. Envia un correo HTML con el enlace
5. Retorna true si el envio es exitoso

---

## Middleware (middleware/)

### middleware/auth.php

Verifica que el usuario tenga una sesion activa.

**Funcionamiento:**
1. Ejecuta `session_start()`
2. Verifica que `$_SESSION['autenticado']` exista y sea `true`
3. Si no esta autenticado, redirige a `/SistemaUsuarios/public/index.html`

**Uso:**
```php
require __DIR__ . "/../../middleware/auth.php";
```

---

### middleware/rol_admin.php

Verifica que el usuario tenga rol de administrador.

**Funcionamiento:**
1. Incluye `auth.php` (primero verifica autenticacion y sesion)
2. Verifica que `$_SESSION['rol']` sea `1` (administrador)
3. Si no es admin, retorna error 403

**Uso:**
```php
require __DIR__ . "/../../middleware/rol_admin.php";
```

---

## API de Autenticacion (api/auth/)

### login.php

Autentica a un usuario con correo y contrasena.

- **Metodo HTTP:** POST
- **Middleware:** Ninguno (publico)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "correo": "usuario@ejemplo.com",
    "contrasena": "miContrasena123"
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Valida que los campos `correo` y `contrasena` esten presentes
3. Busca el usuario por correo en la tabla `Credencial`
4. Verifica la contrasena usando `password_verify()`
5. Si es valido, inicia sesion con `session_start()`
6. Guarda en sesion: `idUsuario` (int), `IdCredencial` (int), `nombre`, `apellido`, `rol` (int), `correo`, `autenticado`
7. Retorna JSON de exito

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Sesion iniciada correctamente"
}
```

**Salida error:**
```json
{
    "success": false,
    "message": "Credenciales incorrectas"
}
```

---

### logout.php

Cierra la sesion del usuario y redirige al login.

- **Metodo HTTP:** GET
- **Middleware:** Ninguno

**Proceso:**
1. Inicia `session_start()`
2. Limpia las variables de sesion con `session_unset()`
3. Destruye la sesion con `session_destroy()`
4. Redirige a `../../public/index.html`

**Nota:** No retorna JSON, solo redirige.

---

### solicitar_token.php

Genera un token de un solo uso y lo envia al correo del usuario.

- **Metodo HTTP:** POST
- **Middleware:** Ninguno (publico)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "correo": "usuario@ejemplo.com"
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Valida que el campo `correo` este presente
3. Busca el usuario por correo en la tabla `Credencial`
4. Genera un token usando `TokenService::crearToken()`
5. Envia el enlace de inicio de sesion usando `MailService::enviarLoginLink()`
6. Retorna JSON de exito

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Enlace de inicio de sesion generado correctamente"
}
```

---

### verificar_token_email.php

Valida un token enviado por correo electronico y crea la sesion.

- **Metodo HTTP:** GET (token via URL)
- **Middleware:** Ninguno (publico)

**Parametros URL:**
```
?token=abc123def456...
```

**Proceso:**
1. Valida que el parametro `token` exista en la URL
2. Ejecuta UPDATE condicional (marca como usado solo si no lo esta):
   - Filtra por token, `Usado = FALSE` y `FechaExpiracion > NOW()`
   - Usa `rowCount()` para detectar condiciones de carrera
3. Si `rowCount() === 0` → token invalido o ya usado
4. Si es valido → obtiene datos del usuario con JOIN
5. Inicia sesion con los datos del usuario (casts a int para `idUsuario`, `IdCredencial`, `rol`)
6. Redirige al dashboard

**Nota de seguridad:** El UPDATE condicional previene que dos personas usen el mismo token simultaneamente.

---

## API de Usuarios (api/usuarios/)

### listarUsuarios.php

Lista todos los usuarios del sistema.

- **Metodo HTTP:** GET
- **Middleware:** rol_admin (solo administradores)
- **Content-Type:** application/json

**SQL:**
```sql
SELECT 
    u.IdUsuario,
    u.PrimerNombre,
    u.SegundoNombre,
    u.PrimerApellido,
    u.SegundoApellido,
    c.NombreUsuario,
    c.Correo,
    r.Descripcion AS Rol
FROM Usuario u
JOIN Credencial c ON u.IdCredencial = c.IdCredencial
JOIN Rol r ON u.IdRol = r.IdRol
```

**Salida:**
```json
[
    {
        "IdUsuario": 1,
        "PrimerNombre": "Juan",
        "SegundoNombre": "Carlos",
        "PrimerApellido": "Perez",
        "SegundoApellido": "Gomez",
        "NombreUsuario": "jperez",
        "Correo": "juan@ejemplo.com",
        "Rol": "administrador"
    }
]
```

---

### obtenerUsuario.php

Obtiene los datos de un usuario especifico.

- **Metodo HTTP:** POST
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdUsuario": 5
}
```

**Nota:** Si no se envia `IdUsuario`, usa `$_SESSION['idUsuario']` (el usuario ve su propio perfil). Solo el admin puede ver perfiles de otros usuarios.

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Resuelve el usuario objetivo (JSON o sesion)
3. Valida permisos (admin puede ver todos, usuario solo el suyo)
4. Retorna datos del usuario

**Salida:**
```json
{
    "success": true,
    "usuario": {
        "IdUsuario": 5,
        "PrimerNombre": "Maria",
        "IdCredencial": 3,
        "Correo": "maria@ejemplo.com"
    }
}
```

---

### crear.php

Crea un nuevo usuario en el sistema.

- **Metodo HTTP:** POST
- **Middleware:** rol_admin (solo administradores)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "PrimerNombre": "Juan",
    "SegundoNombre": "Carlos",
    "PrimerApellido": "Perez",
    "SegundoApellido": "Gomez",
    "IdRol": 2,
    "NombreUsuario": "jperez",
    "Correo": "juan@ejemplo.com",
    "contrasena": "miContrasena123"
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Valida que todos los campos requeridos esten presentes
3. Hashea la contrasena con `password_hash(PASSWORD_DEFAULT)`
4. Inicia transaccion
5. INSERT en `Credencial` (NombreUsuario, Correo, contrasena)
6. Obtiene el `IdCredencial` generado
7. INSERT en `Usuario` (nombres, IdRol, IdCredencial)
8. Confirma transaccion o hace rollback

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Usuario creado correctamente"
}
```

---

### editar.php

Edita los datos de un usuario.

- **Metodo HTTP:** POST
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdUsuario": 5,
    "IdCredencial": 3,
    "PrimerNombre": "Juan",
    "SegundoNombre": "Carlos",
    "PrimerApellido": "Perez",
    "SegundoApellido": "Lopez",
    "Correo": "nuevo@ejemplo.com"
}
```

**Permisos:**
| Campo | Admin | Usuario |
|-------|-------|---------|
| PrimerNombre | Cualquier usuario | Solo si mismo |
| SegundoNombre | Cualquier usuario | Solo si mismo |
| PrimerApellido | Cualquier usuario | Solo si mismo |
| SegundoApellido | Cualquier usuario | Solo si mismo |
| Correo | Solo admin puede cambiar | Solo si mismo |

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Resuelve el usuario objetivo
3. Valida permisos (no admin solo puede editarse a si mismo)
4. Transaccion:
   - UPDATE `Usuario` (nombres y apellidos)
   - UPDATE `Credencial` (solo si es admin)

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Usuario editado correctamente"
}
```

---

### eliminar.php

Elimina un usuario del sistema.

- **Metodo HTTP:** POST
- **Middleware:** rol_admin (solo administradores)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdUsuario": 5,
    "IdCredencial": 3
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Transaccion:
   - DELETE de `Usuario` (primero, por las FK)
   - DELETE de `Credencial` (despues)
3. Rollback si falla alguno

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Usuario eliminado correctamente"
}
```

---

### cambiar_contrasena.php

Permite al usuario cambiar su propia contrasena.

- **Metodo HTTP:** POST
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "Contrasena_Actual": "antigua123",
    "Contrasena_Nueva": "nueva456"
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Obtiene `IdCredencial` de la sesion
3. Busca la contrasena actual en BD
4. Verifica con `password_verify()`
5. Si no coincide → error
6. Si coincide → hashea la nueva y actualiza

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Contrasena cambiada correctamente"
}
```

**Salida error:**
```json
{
    "success": false,
    "message": "La contrasena no es la correcta"
}
```

---

## API de Tareas (api/tareas/)

### listar.php

Lista tareas segun el rol del usuario.

- **Metodo HTTP:** GET
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Comportamiento por rol:**
- **Admin:** Lista todas las tareas
- **Usuario:** Lista solo las tareas asignadas a el (`WHERE t.IdUsuario = ?`)

**SQL (admin):**
```sql
SELECT 
    t.IdTarea,
    t.Titulo,
    t.Descripcion,
    t.Estado,
    t.FechaCreacion,
    t.FechaFin,
    CONCAT(u.PrimerNombre, ' ', u.PrimerApellido) AS Usuario
FROM Tarea AS t
JOIN Usuario u ON u.IdUsuario = t.IdUsuario
```

**SQL (usuario):**
```sql
SELECT 
    t.IdTarea,
    t.Titulo,
    t.Descripcion,
    t.Estado,
    t.FechaCreacion,
    t.FechaFin,
    CONCAT(u.PrimerNombre, ' ', u.PrimerApellido) AS Usuario
FROM Tarea AS t
JOIN Usuario u ON u.IdUsuario = t.IdUsuario
WHERE t.IdUsuario = ?
```

**Salida:**
```json
[
    {
        "IdTarea": 1,
        "Titulo": "Completar reporte",
        "Descripcion": "El reporte mensual",
        "Estado": "pendiente",
        "FechaCreacion": "2026-08-11 10:30:00",
        "FechaFin": "2026-08-15 23:59:00",
        "Usuario": "Juan Perez"
    }
]
```

---

### obtenerTarea.php

Obtiene los datos de una tarea especifica.

- **Metodo HTTP:** POST
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdTarea": 1
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Obtiene la tarea por su ID
3. Valida permisos:
   - Admin: puede ver cualquier tarea
   - Usuario: solo puede ver sus propias tareas
4. Retorna los datos de la tarea

**Salida:**
```json
{
    "success": true,
    "tarea": {
        "IdTarea": 1,
        "IdCreador": 1,
        "IdUsuario": 3,
        "Titulo": "Completar reporte",
        "Descripcion": "El reporte mensual",
        "Estado": "pendiente",
        "FechaCreacion": "2026-08-11 10:30:00",
        "FechaFin": "2026-08-15 23:59:00"
    }
}
```

---

### crear.php

Crea una nueva tarea.

- **Metodo HTTP:** POST
- **Middleware:** rol_admin (solo administradores)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdUsuario": 3,
    "Titulo": "Completar reporte",
    "Descripcion": "El reporte mensual de ventas",
    "FechaFin": "2026-08-20"
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Valida campos obligatorios (`IdUsuario`, `Titulo`)
3. `IdCreador` se toma de `$_SESSION["idUsuario"]`
4. `Estado` se asigna automaticamente como `'pendiente'`
5. INSERT en `Tarea`

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Tarea creada correctamente"
}
```

---

### editar.php

Edita una tarea existente.

- **Metodo HTTP:** POST
- **Middleware:** auth (cualquier usuario autenticado)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdTarea": 1,
    "IdUsuario": 4,
    "Titulo": "Nuevo titulo",
    "Descripcion": "Nueva descripcion",
    "Estado": "en_progreso",
    "FechaFin": "2026-08-25"
}
```

**Permisos:**
| Campo | Admin | Usuario |
|-------|-------|---------|
| IdUsuario | Si | No |
| Titulo | Si | No |
| Descripcion | Si | No |
| Estado | Si | Si (solo sus tareas) |
| FechaFin | Si | No |

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. Si no es admin: UPDATE solo del campo `Estado` (valida que la tarea sea suya)
3. Si es admin: UPDATE de todos los campos

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Tarea editada correctamente"
}
```

---

### eliminar.php

Elimina una tarea del sistema.

- **Metodo HTTP:** POST
- **Middleware:** rol_admin (solo administradores)
- **Content-Type:** application/json

**Entrada (JSON):**
```json
{
    "IdTarea": 1
}
```

**Proceso:**
1. Valida que el JSON sea valido (`json_decode`)
2. DELETE de `Tarea` filtrando por `IdTarea`

**Salida exitosa:**
```json
{
    "success": true,
    "message": "Tarea eliminada correctamente"
}
```

---

## Sistema de Roles

| Rol | Descripcion | Valor en BD |
|-----|-------------|-------------|
| Administrador | Acceso completo a todas las funciones | `1` |
| Empleado | Puede cambiar sus datos personales y ver sus tareas | `2` |

### Permisos por endpoint:

| Endpoint | Admin | Usuario |
|----------|-------|---------|
| `usuarios/listarUsuarios.php` | Si | No |
| `usuarios/obtenerUsuario.php` | Si (todos) | Si (solo propio) |
| `usuarios/crear.php` | Si | No |
| `usuarios/editar.php` | Si (todos los campos) | Si (solo nombres, propio) |
| `usuarios/eliminar.php` | Si | No |
| `usuarios/cambiar_contrasena.php` | Si | Si (solo propia) |
| `tareas/listar.php` | Si (todas) | Si (solo suyas) |
| `tareas/obtenerTarea.php` | Si (todas) | Si (solo suyas) |
| `tareas/crear.php` | Si | No |
| `tareas/editar.php` | Si (todos los campos) | Si (solo estado, suyas) |
| `tareas/eliminar.php` | Si | No |

---

## Como Probar el Backend

### Opcion 1: Con Postman

1. Abre Postman
2. Crea una nueva peticion POST
3. URL: `http://localhost/SistemaUsuarios/api/auth/login.php`
4. En la pestana "Body":
   - Selecciona "raw"
   - Cambia el tipo a "JSON"
   - Escribe:
   ```json
   {
       "correo": "admin@ejemplo.com",
       "contrasena": "admin123"
   }
   ```
5. Envia la peticion

### Opcion 2: Con cURL (terminal)

**Login:**
```bash
curl -X POST http://localhost/SistemaUsuarios/api/auth/login.php `
  -H "Content-Type: application/json" `
  -d "{\"correo\":\"admin@ejemplo.com\",\"contrasena\":\"admin123\"}"
```

**Listar usuarios (requiere cookie de sesion):**
```bash
curl -X GET http://localhost/SistemaUsuarios/api/usuarios/listarUsuarios.php `
  -b "PHPSESSID=tu_session_id"
```

### Opcion 3: Con JavaScript (fetch)

```javascript
// Login
fetch('http://localhost/SistemaUsuarios/api/auth/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        correo: 'admin@ejemplo.com',
        contrasena: 'admin123'
    }),
    credentials: 'include'  // Importante para enviar cookies de sesion
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Flujo de Prueba Completo

### 1. Iniciar sesion como admin
```
POST /api/auth/login.php
{
    "correo": "admin@ejemplo.com",
    "contrasena": "admin123"
}
```
**Respuesta esperada:**
```json
{
    "success": true,
    "message": "Sesion iniciada correctamente"
}
```

### 2. Crear un usuario
```
POST /api/usuarios/crear.php
{
    "PrimerNombre": "Maria",
    "SegundoNombre": "",
    "PrimerApellido": "Lopez",
    "SegundoApellido": "Garcia",
    "IdRol": 2,
    "NombreUsuario": "mlopez",
    "Correo": "maria@ejemplo.com",
    "contrasena": "usuario123"
}
```

### 3. Listar usuarios
```
GET /api/usuarios/listarUsuarios.php
```

### 4. Crear una tarea
```
POST /api/tareas/crear.php
{
    "IdUsuario": 2,
    "Titulo": "Revisar documentos",
    "Descripcion": "Revisar los documentos del proyecto",
    "FechaFin": "2026-12-31"
}
```

### 5. Cerrar sesion
```
GET /api/auth/logout.php
```
(Redirige al login)

### 6. Probar login con token
```
POST /api/auth/solicitar_token.php
{
    "correo": "maria@ejemplo.com"
}
```
**Luego revisa el correo y haz clic en el enlace.**

---

## Instalacion

1. Clonar el repositorio
2. Ejecutar `composer install` para instalar dependencias
3. Ejecutar `sql/creardb.sql` en MySQL
4. Configurar `.env` con las credenciales de base de datos y SMTP
5. Apuntar Apache al directorio del proyecto
6. Abrir `http://localhost/SistemaUsuarios/public/index.html`
