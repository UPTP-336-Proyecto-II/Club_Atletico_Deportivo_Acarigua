# Club Atlético Deportivo Acarigua (CADA) - Legacy Context & Architecture Guide

Este archivo documenta la arquitectura, convenciones de diseño y estado actual del proyecto. Su objetivo es servir de contexto ("memoria") para agentes de IA (como Claude en Antigravity) que trabajen en este proyecto desde otra PC.

---

## 1. Arquitectura del Sistema (MVC Personalizado)

El sistema **NO utiliza frameworks comerciales** (como Laravel o Symfony), sino un framework MVC nativo en PHP 8+ construido a la medida. 

### Estructura de Carpetas Clave:
- `app/Core/`: Núcleo del framework. Contiene clases vitales: `Router`, `Request`, `Response`, `Controller`, `Model`, `Database`, `Auth`, `Validator`. **No modificar a menos que sea un cambio de infraestructura.**
- `app/Controllers/Web/`: Controladores que renderizan vistas HTML completas.
- `app/Controllers/Api/`: Controladores que devuelven JSON puros (usados para Selects dinámicos, gráficos, etc.).
- `app/Models/`: Wrappers directos de PDO (cada clase representa una tabla y extiende de `Model`).
- `app/Views/`: Plantillas nativas en `.php`.
  - `layouts/`: `admin.php` (panel) y `public.php` (landing).
  - `partials/`: Fragmentos reutilizables (`sidebar.php`, `flash.php`).
- `config/`: Contiene `routes.php` (enrutador centralizado). **Toda ruta nueva DEBE registrarse aquí.**
- `public/`: Punto de entrada (Front Controller `index.php`) y directorio de `assets/` estáticos.

---

## 2. Convenciones de Diseño y UI (Estándar CADA)

### 2.1. Tipografía e Iconografía (Offline)
- **Fuentes:** Se usan fuentes descargadas localmente (sin CDNs). `Inter` para cuerpos de texto y `Outfit` para títulos (`var(--font-display)`).
- **Iconos:** Se utiliza la librería **Phosphor Icons** (local).
  - Sintaxis estándar: `<i class="ph ph-nombre-icono"></i>`.
  - Iconos comunes: `ph-user`, `ph-floppy-disk` (guardar), `ph-arrow-left` (volver), `ph-trash` (eliminar).

### 2.2. Clases CSS y Variables Globales (`main.css` y `admin.css`)
- **Colores:**
  - `var(--color-primary)`: Color principal (Rojo/Vino CADA).
  - `var(--color-bg)`: Fondo general.
  - `var(--color-surface)`: Fondo de tarjetas (blanco o gris oscuro en modo noche).
  - `var(--color-border)`: Bordes sutiles.
  - `var(--color-text)` y `var(--color-text-muted)`: Colores de texto automáticos según tema.
  - `var(--color-danger)`, `var(--color-success)`: Estados.
- **Botones Estándar:**
  - `.btn .btn-primary`: Acción principal (Guardar, Registrar).
  - `.btn .btn-outline`: Acción secundaria con bordes.
  - `.btn .btn-ghost`: Botón sin fondo (Cancelar, Atrás).
  - `.btn .btn-danger`: Botón rojo de alerta (Eliminar).

### 2.3. Estructura Estándar de Formularios
Todo formulario nuevo **debe** replicar esta estructura HTML (sin modales, usando Vistas Dedicadas / MPA):

```html
<form method="POST" action="<?= e(url('/admin/entidad')) ?>" class="card" style="max-width: 900px;">
    <?= csrf_field() ?> <!-- Obligatorio -->

    <!-- Fila de 2 columnas -->
    <div class="form-row">
        <!-- Grupo de Input -->
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Nombre del Campo</label>
            <input type="text" name="campo" class="form-control" value="<?= e(old('campo', $item['campo'] ?? '')) ?>" required>
            <?php if (isset($errors['campo'])): ?>
                <div class="form-error"><?= e($errors['campo']) ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Otro Grupo... -->
    </div>

    <!-- Botonera -->
    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
        <a href="<?= e(url('/admin/entidad')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Cancelar</a>
        <button type="submit" class="btn btn-primary"><i class="ph ph-floppy-disk"></i> Guardar</button>
    </div>
</form>
```

---

## 3. Funciones PHP Globales de Ayuda (Helpers)
En las vistas de CADA, se usan estos helpers globales para mantener el código limpio:
- `e($string)`: Escapa variables para prevenir ataques XSS. (Ej: `<?= e($usuario['nombre']) ?>`).
- `url($path)`: Genera URL absoluta. (Ej: `url('/admin/usuarios')`).
- `asset($path)`: Enlaza archivos estáticos. (Ej: `asset('css/admin.css')`).
- `old($key, $default)`: Recupera valores de formularios fallidos.
- `auth()`: Devuelve el array del usuario logueado actualmente.
- `flash('tipo', 'Mensaje')`: Para mostrar notificaciones. Tipos: `success`, `error`, `info`.

---

## 4. Contexto de Lógica Reciente (Artefactos y Flujos)

### 4.1. Flujo de Autenticación y Seguridad (Primer Login)
- Se eliminó el registro de usuarios públicos. Los administradores crean usuarios.
- La contraseña por defecto son los números de la cédula del usuario.
- **Flujo Setup:** Al hacer el primer login (detectado porque la clave sin hashear es igual a la cédula), se activa `$_SESSION['must_change_password']`.
- El middleware `AuthMiddleware` detecta esto y **bloquea todas las rutas**, forzando al usuario a ir a `/admin/setup`.
- En el Setup, el usuario debe crear una nueva clave (BCrypt) y seleccionar 2 preguntas de seguridad.

### 4.2. Módulo "Mi Perfil"
- Ubicado en `/admin/perfil`. Accesible desde el menú desplegable superior derecho (avatar).
- **No se usan modales.** Se usa una vista con 2 pestañas JS: "Datos Personales" y "Seguridad".
- Se implementaron validaciones dinámicas JS: longitud de teléfono (max 7), regex de correo, validación visual de contraseñas iguales, y bloqueo de subida de imágenes pesadas (>2MB).

### 4.3. Cascada de Direcciones Dinámicas
- La selección de direcciones (Estado -> Municipio -> Parroquia) funciona de manera dinámica.
- El frontend usa JS (`fetch`) contra la API en `/api/direcciones/...` para cargar los `selects` sin recargar la página.

### 4.4. Base de Datos (Últimos Ajustes)
- `preguntas_seguridad.preguntas` ampliado a `VARCHAR(100)`. Agrupadas en 4 categorías (Infancia, Preferencias, Familia, Deporte).
- `respuestas_seguridad.respuesta` ampliado a `VARCHAR(255)` para almacenar correctamente los hashes BCrypt (costo 12).
- Contraseñas almacenadas usando `password_hash($pass, PASSWORD_BCRYPT)`. Validaciones usando `password_verify`.

---

> **NOTA PARA LA IA (CLAUDE/ANTIGRAVITY):** 
> Al crear nuevas vistas, prioriza la estética. Usa el modo oscuro nativo que proveen las variables de CSS. No incluyas Bootstrap, Tailwind ni bibliotecas externas. Todo debe basarse en el `main.css` y `admin.css` existentes. Si requieres mostrar listas de datos, usa la estructura de tablas de CADA (con el div `.table-responsive`).
