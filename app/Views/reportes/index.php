<?php /** @var array $stats @var array $atletas @var array $usuarios @var array $categorias */ 
if (!function_exists('formatDocumento')) {
    function formatDocumento($cedula) {
        if (empty($cedula)) {
            return '—';
        }
        $cedula = trim($cedula);
        if (preg_match('/^[VEPvep]-?\d+/', $cedula)) {
            $prefix = strtoupper($cedula[0]);
            $number = ltrim(substr($cedula, 1), '-');
            return $prefix . '-' . $number;
        }
        if (ctype_digit($cedula)) {
            return 'V-' . $cedula;
        }
        return $cedula;
    }
}
?>
<div class="page-header">
    <div>
        <h1>Centro de Reportes y Estadísticas</h1>
        <div class="subtitle">Generación de fichas, exportación de datos y analíticas de asistencia</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 350px; gap: 24px; align-items: start;">
    <!-- Main Content: Buscadores y Pestañas -->
    <div class="card" style="padding: 24px; min-height: 500px; min-width: 0;">
        
        <!-- Pestañas (Tabs) -->
        <div style="display: flex; gap: 16px; border-bottom: 2px solid var(--color-border); margin-bottom: 24px;">
            <button type="button" class="tab-btn active" data-target="tab-atletas" style="padding: 12px 24px; font-weight: 600; border: 0; background: transparent; border-bottom: 3px solid var(--color-primary); color: var(--color-primary); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                <i class="ph ph-users-three" style="font-size: 18px;"></i> Atletas
            </button>
            <?php if (can('admin')): ?>
            <button type="button" class="tab-btn" data-target="tab-usuarios" style="padding: 12px 24px; font-weight: 600; border: 0; background: transparent; border-bottom: 3px solid transparent; color: var(--color-text-muted); cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                <i class="ph ph-shield-chevron" style="font-size: 18px;"></i> Usuarios
            </button>
            <?php endif; ?>
        </div>

        <!-- Panel de Atletas -->
        <div id="tab-atletas" class="tab-content-pane">
            <!-- Buscador y Filtros de Atletas -->
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <input type="text" id="search-atleta" class="form-control" placeholder="Buscar atleta por nombre o documento..." style="padding-left: 36px;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); pointer-events: none;"></i>
                </div>
                <div style="width: 180px;">
                    <select id="filter-cat" class="form-control">
                        <option value="">Todas las Categorías</option>
                        <?php foreach (($categorias ?? []) as $c): ?>
                            <option value="<?= (int) $c['categoria_id'] ?>"><?= e($c['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="width: 150px;">
                    <select id="filter-estatus-atleta" class="form-control">
                        <option value="">Todos los Estatus</option>
                        <?php foreach (ESTATUS_ATLETA as $k => $v): ?>
                            <option value="<?= $k ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Tabla de Atletas -->
            <div class="data-table-wrap">
                <table class="data-table" style="margin: 0; border: none;">
                    <thead>
                        <tr>
                            <th style="padding-left: 12px;">Atleta</th>
                            <th>Documento</th>
                            <th>Categoría</th>
                            <th>Estatus</th>
                            <th style="width: 250px; text-align: right; padding-right: 12px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($atletas as $a): ?>
                        <tr class="atleta-row" data-name="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>" data-cedula="<?= e($a['cedula'] ?? '') ?>" data-categoria="<?= (int)$a['categoria_id'] ?>" data-estatus="<?= (int)$a['estatus'] ?>">
                            <td style="padding-left: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if (!empty($a['foto'])): ?>
                                        <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; background: var(--color-primary-light); color: var(--color-primary); flex-shrink: 0;">
                                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <strong style="color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></strong>
                                </div>
                            </td>
                            <td><span style="color: var(--color-text-muted); font-size: 13px;"><i class="ph ph-identification-card"></i> <?= e(formatDocumento($a['cedula'] ?? '')) ?></span></td>
                            <td><span style="font-weight: 500; font-size: 13px;"><?= e($a['nombre_categoria'] ?? 'Sin Categoría') ?></span></td>
                            <td>
                                <?php
                                $estText = ESTATUS_ATLETA[(int)$a['estatus']] ?? 'Desconocido';
                                $estBadge = match((int)$a['estatus']) {
                                    1 => 'badge-success',
                                    2 => 'badge-warning',
                                    0 => 'badge-danger',
                                    default => 'badge-secondary'
                                };
                                ?>
                                <span class="badge <?= $estBadge ?>"><?= e($estText) ?></span>
                            </td>
                            <td style="text-align: right; padding-right: 12px;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button type="button" class="btn btn-sm btn-ghost" onclick="openModalAsistAtleta(<?= (int)$a['atleta_id'] ?>, '<?= e(addslashes($a['nombre'] . ' ' . $a['apellido'])) ?>')" title="Imprimir Asistencia">
                                        <i class="ph ph-calendar-check" style="font-size: 16px;"></i> Asistencia
                                    </button>
                                    <a href="<?= e(url("/admin/reportes/atleta/{$a['atleta_id']}")) ?>" class="btn btn-sm btn-outline" target="_blank" title="Imprimir Ficha Técnica">
                                        <i class="ph ph-file-pdf"></i> Ficha
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($atletas)): ?>
                        <tr class="no-results-row"><td colspan="5" class="text-center text-muted" style="padding:48px"><i class="ph ph-user-list text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>No hay atletas registrados.</td></tr>
                    <?php endif; ?>
                    <tr id="no-atletas-search" style="display: none;"><td colspan="5" class="text-center text-muted" style="padding:48px"><i class="ph ph-magnifying-glass text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>No se encontraron atletas con esos filtros.</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="atletas-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>
        </div>

        <?php if (can('admin')): ?>
        <!-- Panel de Usuarios -->
        <div id="tab-usuarios" class="tab-content-pane" style="display: none;">
            <!-- Buscador y Filtros de Usuarios -->
            <div style="display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px; position: relative;">
                    <input type="text" id="search-usuario" class="form-control" placeholder="Buscar usuario por nombre o documento..." style="padding-left: 36px;">
                    <i class="ph ph-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); pointer-events: none;"></i>
                </div>
                <div style="width: 180px;">
                    <select id="filter-rol" class="form-control">
                        <option value="">Todos los Roles</option>
                        <option value="1">Superusuario</option>
                        <option value="2">Administrador</option>
                        <option value="3">Entrenador</option>
                    </select>
                </div>
            </div>

            <!-- Tabla de Usuarios -->
            <div class="data-table-wrap">
                <table class="data-table" style="margin: 0; border: none;">
                    <thead>
                        <tr>
                            <th style="padding-left: 12px;">Usuario</th>
                            <th>Documento</th>
                            <th>Rol</th>
                            <th>Estatus</th>
                            <th style="width: 150px; text-align: right; padding-right: 12px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="usuario-row" data-name="<?= e($u['nombre'] . ' ' . $u['apellido']) ?>" data-cedula="<?= e($u['cedula'] ?? '') ?>" data-rol="<?= (int)$u['rol_id'] ?>">
                            <td style="padding-left: 12px;">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <?php if (!empty($u['foto'])): ?>
                                        <img src="<?= e(url($u['foto'])) ?>" class="avatar-thumb" alt="" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
                                    <?php else: ?>
                                        <div class="avatar-placeholder" style="width: 32px; height: 32px; font-size: 12px; background: var(--color-primary-light); color: var(--color-primary); flex-shrink: 0;">
                                            <?= e(mb_substr($u['nombre'], 0, 1) . mb_substr($u['apellido'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <strong style="color: var(--color-text);"><?= e($u['nombre'] . ' ' . $u['apellido']) ?></strong>
                                </div>
                            </td>
                            <td><span style="color: var(--color-text-muted); font-size: 13px;"><i class="ph ph-identification-card"></i> <?= e(formatDocumento($u['cedula'] ?? '')) ?></span></td>
                            <td>
                                <?php
                                $rolText = match((int)$u['rol_id']) {
                                    1 => 'Superusuario',
                                    2 => 'Administrador',
                                    3 => 'Entrenador',
                                    default => 'Desconocido'
                                };
                                ?>
                                <span style="font-weight: 500; font-size: 13px;"><?= e($rolText) ?></span>
                            </td>
                            <td>
                                <?php
                                $uEst = $u['estatus'] ?? 'Activo';
                                $isActive = (strcasecmp((string)$uEst, 'activo') === 0 || $uEst === '1' || $uEst === 1);
                                $uEstText = $isActive ? 'Activo' : 'Inactivo';
                                $uEstBadge = $isActive ? 'badge-success' : 'badge-secondary';
                                ?>
                                <span class="badge <?= $uEstBadge ?>"><?= e($uEstText) ?></span>
                            </td>
                            <td style="text-align: right; padding-right: 12px;">
                                <a href="<?= e(url("/admin/reportes/usuario/{$u['usuario_id']}")) ?>" class="btn btn-sm btn-outline" target="_blank" title="Imprimir Ficha de Usuario">
                                    <i class="ph ph-file-pdf"></i> Ficha
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($usuarios)): ?>
                        <tr class="no-results-row"><td colspan="5" class="text-center text-muted" style="padding:48px"><i class="ph ph-user-list text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                    <tr id="no-usuarios-search" style="display: none;"><td colspan="5" class="text-center text-muted" style="padding:48px"><i class="ph ph-magnifying-glass text-muted" style="font-size:32px; display:block; margin-bottom:8px; opacity:0.5;"></i>No se encontraron usuarios con esos filtros.</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="usuarios-pagination" style="display: flex; justify-content: center; margin-top: 24px;"></div>
        </div>
        <?php endif; ?>

    </div>

    <!-- Sidebar: Otros Reportes Globales -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card" style="padding: 24px;">
            <h3 style="margin-top: 0; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="ph ph-files" style="color: var(--color-primary); font-size: 20px;"></i> Reportes por Categoría
            </h3>
            <p class="text-muted" style="font-size: 13px; margin-bottom: 20px;">Genera reportes específicos consolidados por categoría deportiva.</p>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <!-- Atletas por Categoría -->
                <button type="button" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%;" onclick="openModalCat('atletas')">
                    <i class="ph ph-file-pdf" style="color: var(--color-info); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Atletas por Categoría</div>
                        <div style="font-size: 11px; opacity: 0.7;">Fichas y listado de la categoría</div>
                    </div>
                </button>

                <!-- Asistencia por Categoría -->
                <button type="button" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%;" onclick="openModalCat('asistencia')">
                    <i class="ph ph-file-pdf" style="color: var(--color-success); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px;">Asistencia por Categoría</div>
                        <div style="font-size: 11px; opacity: 0.7;">Porcentaje de asistencia en rango de fechas</div>
                    </div>
                </button>

                <?php if (can('admin')): ?>
                <!-- Listado de Usuarios (Solo Admin) -->
                <a href="<?= e(url('/admin/reportes/usuarios/listado')) ?>" class="btn btn-outline" style="justify-content: flex-start; padding: 12px 16px; border-width: 2px; text-align: left; width: 100%; text-decoration: none;" target="_blank">
                    <i class="ph ph-file-pdf" style="color: var(--color-warning); font-size: 24px; margin-right: 8px;"></i> 
                    <div>
                        <div style="font-weight: 700; font-size: 14px; color: var(--color-text);">Listado de Usuarios</div>
                        <div style="font-size: 11px; opacity: 0.7; color: var(--color-text-muted);">Personal administrativo y entrenadores</div>
                    </div>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Reportes por Categoría -->
<div class="modal-overlay" id="modal-reporte-cat" style="display: none;">
    <div class="modal-container" style="max-width: 420px; width: 90%;">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title-cat">Generar Reporte</h3>
            <button type="button" class="modal-close" onclick="closeModalCat()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-reporte-cat" target="_blank" method="GET" novalidate>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Categoría Deportiva</label>
                    <select name="categoria" id="cat-select" class="form-control">
                        <option value="">— Seleccione —</option>
                        <?php foreach (($categorias ?? []) as $c): ?>
                            <option value="<?= (int) $c['categoria_id'] ?>"><?= e($c['nombre_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="date-range-fields" style="display:none; gap:12px; margin-top:16px;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label"><span class="required">*</span> Desde</label>
                        <input type="date" name="desde" id="r-desde" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label"><span class="required">*</span> Hasta</label>
                        <input type="date" name="hasta" id="r-hasta" class="form-control" value="<?= date('Y-m-d') ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModalCat()">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-file-pdf"></i> Generar PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Reporte de Asistencia Individual (Atleta) -->
<div class="modal-overlay" id="modal-asistencia-atleta" style="display: none;">
    <div class="modal-container" style="max-width: 420px; width: 90%;">
        <div class="modal-header">
            <h3 class="modal-title" id="modal-title-asist-atleta"><i class="ph ph-calendar-check"></i> Reporte de Asistencia</h3>
            <button type="button" class="modal-close" onclick="closeModalAsistAtleta()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form-asistencia-atleta" target="_blank" method="GET" novalidate>
                <p style="font-size: 13px; color: var(--color-text-muted); margin-bottom: 16px;">
                    Seleccione el rango de fechas para el reporte de <strong id="asist-atleta-nombre"></strong>.
                </p>
                <div style="display:flex; gap:12px;">
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Desde</label>
                        <input type="date" name="desde" id="asist-desde" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group" style="flex:1">
                        <label class="form-label">Hasta</label>
                        <input type="date" name="hasta" id="asist-hasta" class="form-control" min="2019-01-01" max="<?= date('Y-m-d') ?>">
                    </div>
                </div>
                <p style="font-size: 11px; color: var(--color-text-muted); margin-top: 8px;">
                    * Si se dejan en blanco, el reporte detallará el mes actual y resumirá el año.
                </p>

                <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:24px;">
                    <button type="button" class="btn btn-ghost" onclick="closeModalAsistAtleta()">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="ph ph-file-pdf"></i> Generar PDF</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Pestañas (Tabs)
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('active');
            b.style.borderBottomColor = 'transparent';
            b.style.color = 'var(--color-text-muted)';
        });
        document.querySelectorAll('.tab-content-pane').forEach(p => p.style.display = 'none');

        this.classList.add('active');
        this.style.borderBottomColor = 'var(--color-primary)';
        this.style.color = 'var(--color-primary)';
        document.getElementById(this.dataset.target).style.display = 'block';
    });
});

// Buscador y filtros de Atletas con Paginación
const $searchAtleta = document.getElementById('search-atleta');
const $filterCat = document.getElementById('filter-cat');
const $filterEstAtleta = document.getElementById('filter-estatus-atleta');
const $rowsAtletas = document.querySelectorAll('.atleta-row');
const $noAtletasSearch = document.getElementById('no-atletas-search');

const rowsPerPage = 15;
let currentAtletasPage = 1;

function filterAtletas() {
    const q = $searchAtleta.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    const cat = $filterCat.value;
    const est = $filterEstAtleta.value;

    $rowsAtletas.forEach(row => {
        const name = row.dataset.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const cedula = row.dataset.cedula.toLowerCase();
        const rowCat = row.dataset.categoria;
        const rowEst = row.dataset.estatus;

        const matchQ = !q || name.includes(q) || cedula.includes(q);
        const matchCat = !cat || rowCat === cat;
        const matchEst = !est || rowEst === est;

        row.dataset.matched = (matchQ && matchCat && matchEst) ? '1' : '0';
    });

    paginateAtletas(1);
}

function paginateAtletas(page) {
    currentAtletasPage = page;
    const matchedRows = Array.from($rowsAtletas).filter(row => row.dataset.matched === '1');
    const totalCount = matchedRows.length;
    const totalPages = Math.ceil(totalCount / rowsPerPage);

    if (totalCount === 0 && $rowsAtletas.length > 0) {
        $noAtletasSearch.style.display = '';
    } else {
        $noAtletasSearch.style.display = 'none';
    }

    $rowsAtletas.forEach(row => row.style.display = 'none');

    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    matchedRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });

    renderPagination('atletas-pagination', page, totalPages, paginateAtletas);
}

$searchAtleta.addEventListener('input', filterAtletas);
$filterCat.addEventListener('change', filterAtletas);
$filterEstAtleta.addEventListener('change', filterAtletas);

// Buscador y filtros de Usuarios con Paginación
const $searchUsuario = document.getElementById('search-usuario');
const $filterRol = document.getElementById('filter-rol');
const $rowsUsuarios = document.querySelectorAll('.usuario-row');
const $noUsuariosSearch = document.getElementById('no-usuarios-search');

let currentUsuariosPage = 1;

function filterUsuarios() {
    if (!$rowsUsuarios.length) return;
    const q = $searchUsuario.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
    const rol = $filterRol.value;

    $rowsUsuarios.forEach(row => {
        const name = row.dataset.name.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        const cedula = row.dataset.cedula.toLowerCase();
        const rowRol = row.dataset.rol;

        const matchQ = !q || name.includes(q) || cedula.includes(q);
        const matchRol = !rol || rowRol === rol;

        row.dataset.matched = (matchQ && matchRol) ? '1' : '0';
    });

    paginateUsuarios(1);
}

function paginateUsuarios(page) {
    currentUsuariosPage = page;
    const matchedRows = Array.from($rowsUsuarios).filter(row => row.dataset.matched === '1');
    const totalCount = matchedRows.length;
    const totalPages = Math.ceil(totalCount / rowsPerPage);

    if (totalCount === 0 && $rowsUsuarios.length > 0) {
        if ($noUsuariosSearch) $noUsuariosSearch.style.display = '';
    } else {
        if ($noUsuariosSearch) $noUsuariosSearch.style.display = 'none';
    }

    $rowsUsuarios.forEach(row => row.style.display = 'none');

    const startIndex = (page - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;

    matchedRows.forEach((row, index) => {
        if (index >= startIndex && index < endIndex) {
            row.style.display = '';
        }
    });

    renderPagination('usuarios-pagination', page, totalPages, paginateUsuarios);
}

if ($searchUsuario) {
    $searchUsuario.addEventListener('input', filterUsuarios);
    $filterRol.addEventListener('change', filterUsuarios);
}

// Función genérica para renderizar paginación idéntica al directorio de atletas (solo números de página)
function renderPagination(containerId, currentPage, totalPages, onPageChange) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    const ul = document.createElement('ul');
    ul.className = 'pagination';

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        if (i === currentPage) {
            li.className = 'active';
            const span = document.createElement('span');
            span.textContent = i;
            li.appendChild(span);
        } else {
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = i;
            a.onclick = (e) => {
                e.preventDefault();
                onPageChange(i);
            };
            li.appendChild(a);
        }
        ul.appendChild(li);
    }

    container.appendChild(ul);
}

// Modales
function openModalCat(type) {
    const modal = document.getElementById('modal-reporte-cat');
    const title = document.getElementById('modal-title-cat');
    const dateRange = document.getElementById('date-range-fields');

    // Limpiar campos y marcas
    const catSelect = document.getElementById('cat-select');
    catSelect.value = '';
    FormValidator.clearMark(catSelect);
    FormValidator.clearMark(document.getElementById('r-desde'));
    FormValidator.clearMark(document.getElementById('r-hasta'));

    if (type === 'atletas') {
        title.innerHTML = '<i class="ph ph-users-three"></i> Fichas por Categoría';
        dateRange.style.display = 'none';
    } else if (type === 'asistencia') {
        title.innerHTML = '<i class="ph ph-calendar-check"></i> Asistencia por Categoría';
        dateRange.style.display = 'flex';
    }

    modal.style.display = 'flex';
}

function closeModalCat() {
    document.getElementById('modal-reporte-cat').style.display = 'none';
}

// Cierra modal al hacer click fuera del contenedor
document.getElementById('modal-reporte-cat').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalCat();
    }
});

// Validación del formulario de reportes por categoría para evitar tooltips nativos del navegador
document.getElementById('form-reporte-cat').addEventListener('submit', function(e) {
    const catSelect = document.getElementById('cat-select');
    const dateRange = document.getElementById('date-range-fields');
    const desde = document.getElementById('r-desde');
    const hasta = document.getElementById('r-hasta');

    // Validar categoría
    if (!catSelect.value) {
        e.preventDefault();
        FormValidator.markError(catSelect);
        CadaModal.alert({
            title: 'Campo Requerido',
            text: 'Por favor, seleccione una categoría deportiva.',
            type: 'warning'
        });
        return;
    }

    // Configurar acción del formulario dinámicamente según la categoría seleccionada
    if (dateRange.style.display === 'none') {
        this.action = '<?= e(url('/admin/reportes/categoria/')) ?>' + catSelect.value;
    } else {
        this.action = '<?= e(url('/admin/reportes/asistencia/categoria')) ?>';
    }

    // Validar 'desde' si el rango de fechas está visible
    if (dateRange.style.display !== 'none') {
        if (!desde.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Campo Requerido',
                text: 'Por favor, indique la fecha de inicio (Desde).',
                type: 'warning'
            });
            return;
        }

        const today = new Date().toISOString().split('T')[0];
        if (desde.value < '2019-01-01' || desde.value > today) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Desde" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }

        if (hasta.value && (hasta.value < '2019-01-01' || hasta.value > today)) {
            e.preventDefault();
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Hasta" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }

        if (hasta.value && desde.value > hasta.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Rango Inválido',
                text: 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".',
                type: 'warning'
            });
            return;
        }
    }
});

// Limpiar marcas de error en el modal de categorías
document.getElementById('cat-select').addEventListener('change', function() {
    FormValidator.clearMark(this);
});
document.getElementById('r-desde').addEventListener('input', function() {
    FormValidator.clearMark(this);
});

// --- Lógica del Modal de Reporte de Asistencia Individual (Atleta) ---
function openModalAsistAtleta(atletaId, nombreCompleto) {
    const modal = document.getElementById('modal-asistencia-atleta');
    const form = document.getElementById('form-asistencia-atleta');
    document.getElementById('asist-atleta-nombre').textContent = nombreCompleto;
    
    // Configurar acción del formulario dinámicamente
    form.action = '<?= e(url('/admin/reportes/asistencia/atleta/')) ?>' + atletaId;
    
    // Limpiar campos y errores anteriores
    document.getElementById('asist-desde').value = '';
    document.getElementById('asist-hasta').value = '';
    FormValidator.clearMark(document.getElementById('asist-desde'));
    FormValidator.clearMark(document.getElementById('asist-hasta'));

    modal.style.display = 'flex';
}

function closeModalAsistAtleta() {
    document.getElementById('modal-asistencia-atleta').style.display = 'none';
}

// Cierra modal al hacer click fuera del contenedor
document.getElementById('modal-asistencia-atleta').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModalAsistAtleta();
    }
});

// Validación del formulario de reporte de asistencia de atleta para asegurar rango lógico e interdependencia de fechas
document.getElementById('form-asistencia-atleta').addEventListener('submit', function(e) {
    const desde = document.getElementById('asist-desde');
    const hasta = document.getElementById('asist-hasta');

    // Validación de interdependencia: si se selecciona una, la otra es obligatoria
    if ((desde.value && !hasta.value) || (!desde.value && hasta.value)) {
        e.preventDefault();
        if (!desde.value) FormValidator.markError(desde);
        if (!hasta.value) FormValidator.markError(hasta);
        CadaModal.alert({
            title: 'Fechas Incompletas',
            text: 'Si selecciona una fecha de inicio o fin, debe especificar ambas fechas para definir el rango.',
            type: 'warning'
        });
        return;
    }

    if (desde.value || hasta.value) {
        const today = new Date().toISOString().split('T')[0];
        if (desde.value && (desde.value < '2019-01-01' || desde.value > today)) {
            e.preventDefault();
            FormValidator.markError(desde);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Desde" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }
        if (hasta.value && (hasta.value < '2019-01-01' || hasta.value > today)) {
            e.preventDefault();
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Fecha Inválida',
                text: 'La fecha "Hasta" debe estar entre el 01/01/2019 y el día de hoy.',
                type: 'warning'
            });
            return;
        }
        if (desde.value && hasta.value && desde.value > hasta.value) {
            e.preventDefault();
            FormValidator.markError(desde);
            FormValidator.markError(hasta);
            CadaModal.alert({
                title: 'Rango Inválido',
                text: 'La fecha "Desde" no puede ser posterior a la fecha "Hasta".',
                type: 'warning'
            });
            return;
        }
    }
});

// Limpiar marcas de error al interactuar en el modal del atleta
document.getElementById('asist-desde').addEventListener('input', function() {
    FormValidator.clearMark(this);
    FormValidator.clearMark(document.getElementById('asist-hasta'));
});
document.getElementById('asist-hasta').addEventListener('input', function() {
    FormValidator.clearMark(this);
    FormValidator.clearMark(document.getElementById('asist-desde'));
});

// Inicializar paginación al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    filterAtletas();
    if ($searchUsuario) {
        filterUsuarios();
    }
});
</script>
