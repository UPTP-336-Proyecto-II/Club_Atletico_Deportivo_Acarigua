<?php
/** @var array|null $item @var array $roles @var string $action */
$p = $item ?? [];
$get = fn(string $k, $d = '') => old($k, $p[$k] ?? $d);
$isEdit = !empty($p['usuario_id']);

$maxDate = date('Y-m-d', strtotime('-18 years'));
?>
<div class="page-header">
    <div>
        <h1><?= $isEdit ? 'Editar' : 'Nuevo' ?> Usuario</h1>
        <div class="subtitle">Complete los datos personales y de ubicación</div>
    </div>
    <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
</div>

<form method="POST" action="<?= e($action) ?>" enctype="multipart/form-data" class="card" style="max-width:1000px; padding:0; overflow:hidden;">
    <?= csrf_field() ?>

    <!-- Indicador de pasos -->
    <div class="form-tabs" style="background: var(--color-bg-alt); padding: 16px 24px; border-bottom: 1px solid var(--color-border); display: flex; gap: 32px;">
        <span id="step-indicator-1" style="font-weight: 600; color: var(--color-primary); border-bottom: 2px solid var(--color-primary); padding-bottom: 12px;">
            <i class="ph ph-user"></i> 1. Datos Personales
        </span>
        <span id="step-indicator-2" style="font-weight: 500; color: var(--color-text-muted); padding-bottom: 12px;">
            <i class="ph ph-map-pin"></i> 2. Ubicación
        </span>
    </div>

    <div style="padding: 32px;">
        <!-- Paso 1: Datos personales -->
        <div id="step-personal" class="form-step">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-identification-card text-muted"></i> Información Básica</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Nombres</label>
                    <input type="text" name="nombre" class="form-control" required maxlength="30" value="<?= e($get('nombre')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Apellidos</label>
                    <input type="text" name="apellido" class="form-control" required maxlength="30" value="<?= e($get('apellido')) ?>">
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Cédula</label>
                    <input type="text" name="cedula" class="form-control" required maxlength="12" pattern="[0-9]+" title="Solo números" value="<?= e($get('cedula')) ?>">
                    <?php if (!$isEdit): ?>
                        <div class="form-hint">Se usará como contraseña inicial.</div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Teléfono</label>
                    <input type="text" name="telefono" class="form-control" required maxlength="15" pattern="[0-9]+" title="Solo números" value="<?= e($get('telefono')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Fecha de nacimiento</label>
                    <input type="date" name="fecha_nac" class="form-control" required max="<?= $maxDate ?>" value="<?= e($get('fecha_nac')) ?>">
                    <div class="form-hint">Debe ser mayor de edad (min. 18 años).</div>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Correo Electrónico</label>
                    <input type="email" name="correo" class="form-control" required maxlength="50" value="<?= e($get('correo')) ?>" placeholder="ejemplo@correo.com">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Rol / Cargo</label>
                    <select name="rol_id" class="form-control" required>
                        <option value="">Selecciona...</option>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= (int) $r['rol_id'] ?>" <?= (int) $get('rol_id') === (int) $r['rol_id'] ? 'selected' : '' ?>>
                                <?= e($r['nombre_rol']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Foto de Perfil</label>
                    <input type="file" name="foto" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <?php if (!empty($p['foto'])): ?>
                        <div class="form-hint mt-2">
                            <img src="<?= e(url($p['foto'])) ?>" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid var(--color-border);">
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <a href="<?= e(url('/admin/usuarios')) ?>" class="btn btn-ghost">Cancelar</a>
                <button type="button" id="btn-siguiente" class="btn btn-primary"><i class="ph ph-arrow-right"></i> Siguiente</button>
            </div>
        </div>

        <!-- Paso 2: Dirección -->
        <div id="step-direccion" class="form-step" style="display: none;">
            <h3 style="margin-top: 0; margin-bottom: 24px; font-family: var(--font-display); color: var(--color-text);"><i class="ph ph-map-pin-line text-muted"></i> Datos de Residencia</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Estado</label>
                    <select id="sel-estado" name="estado_id" class="form-control" data-current="<?= (int) ($p['estado_id'] ?? 0) ?>" required>
                        <option value="">Selecciona Estado...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Municipio</label>
                    <select id="sel-municipio" name="municipio_id" class="form-control" data-current="<?= (int) ($p['municipio_id'] ?? 0) ?>" required>
                        <option value="">Selecciona Municipio...</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Parroquia</label>
                    <select id="sel-parroquia" name="parroquias_id" class="form-control" data-current="<?= (int) ($p['parroquias_id'] ?? 0) ?>" required>
                        <option value="">Selecciona Parroquia...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Tipo de Vivienda</label>
                    <select name="tipo_vivienda" class="form-control" required>
                        <option value="casa" <?= $get('tipo_vivienda') === 'casa' ? 'selected' : '' ?>>Casa</option>
                        <option value="apto" <?= $get('tipo_vivienda') === 'apto' ? 'selected' : '' ?>>Apartamento</option>
                        <option value="edificio" <?= $get('tipo_vivienda') === 'edificio' ? 'selected' : '' ?>>Edificio</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Localidad (Barrio / Urbanización)</label>
                    <input type="text" name="localidad" class="form-control" required maxlength="100" value="<?= e($get('localidad')) ?>" placeholder="Ej: Urb. Villas del Pilar, Barrio San Jose">
                </div>
                <div class="form-group">
                    <label class="form-label"><span class="required">*</span> Dirección Exacta</label>
                    <input type="text" name="ubicacion_vivienda" class="form-control" required maxlength="100" value="<?= e($get('ubicacion_vivienda')) ?>" placeholder="Ej: Calle 15A, Casa 412">
                </div>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <button type="button" id="btn-atras" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Atrás</button>
                <button type="submit" class="btn btn-primary"><i class="ph ph-check"></i> <?= $isEdit ? 'Guardar Cambios' : 'Registrar Usuario' ?></button>
            </div>
        </div>
    </div>
</form>

<script>
// Navegación Wizard (Paso a Paso)
const stepPersonal = document.getElementById('step-personal');
const stepDireccion = document.getElementById('step-direccion');
const indicator1 = document.getElementById('step-indicator-1');
const indicator2 = document.getElementById('step-indicator-2');

document.getElementById('btn-siguiente').addEventListener('click', () => {
    // Validar campos del paso 1 antes de avanzar
    const inputs = stepPersonal.querySelectorAll('[required]');
    let valid = true;
    inputs.forEach(input => { if (!input.reportValidity()) valid = false; });
    if (!valid) return;

    stepPersonal.style.display = 'none';
    stepDireccion.style.display = 'block';
    indicator1.style.fontWeight = '500'; indicator1.style.color = 'var(--color-text-muted)'; indicator1.style.borderBottom = 'none';
    indicator2.style.fontWeight = '600'; indicator2.style.color = 'var(--color-primary)'; indicator2.style.borderBottom = '2px solid var(--color-primary)';
});

document.getElementById('btn-atras').addEventListener('click', () => {
    stepDireccion.style.display = 'none';
    stepPersonal.style.display = 'block';
    indicator2.style.fontWeight = '500'; indicator2.style.color = 'var(--color-text-muted)'; indicator2.style.borderBottom = 'none';
    indicator1.style.fontWeight = '600'; indicator1.style.color = 'var(--color-primary)'; indicator1.style.borderBottom = '2px solid var(--color-primary)';
});

// Carga dinámica de direcciones (cascada Estado → Municipio → Parroquia)
const selEstado = document.getElementById('sel-estado');
const selMunicipio = document.getElementById('sel-municipio');
const selParroquia = document.getElementById('sel-parroquia');

async function loadSelect(sel, url, currentId) {
    try {
        const data = await API.get(url);
        sel.innerHTML = '<option value="">Selecciona...</option>';
        data.forEach(item => {
            const id = item.estado_id || item.municipio_id || item.parroquia_id;
            const name = item.estado || item.municipio || item.parroquia;
            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = name;
            if (parseInt(currentId) === parseInt(id)) opt.selected = true;
            sel.appendChild(opt);
        });
    } catch (e) { console.error('Error cargando select:', e); }
}

// Cargar estados al iniciar (Venezuela tiene un solo país, paisId = 1 no existe, cargamos estados directo)
(async function init() {
    await loadSelect(selEstado, '/api/direcciones/estados/1', selEstado.dataset.current);
    if (selEstado.dataset.current) {
        await loadSelect(selMunicipio, '/api/direcciones/municipios/' + selEstado.dataset.current, selMunicipio.dataset.current);
    }
    if (selMunicipio.dataset.current) {
        await loadSelect(selParroquia, '/api/direcciones/parroquias/' + selMunicipio.dataset.current, selParroquia.dataset.current);
    }
})();

selEstado.addEventListener('change', async () => {
    selMunicipio.innerHTML = '<option value="">Selecciona Municipio...</option>';
    selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
    if (selEstado.value) {
        await loadSelect(selMunicipio, '/api/direcciones/municipios/' + selEstado.value, 0);
    }
});

selMunicipio.addEventListener('change', async () => {
    selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
    if (selMunicipio.value) {
        await loadSelect(selParroquia, '/api/direcciones/parroquias/' + selMunicipio.value, 0);
    }
});
</script>
