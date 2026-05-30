<?php /** @var array $categorias @var array $entrenadores */ ?>
<div class="page-header">
    <div>
        <h1>Registrar Asistencia</h1>
        <div class="subtitle">Selecciona la categoría y registra la asistencia de hoy</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/asistencias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Directorio de Asistencias
        </a>
    </div>
</div>

<form method="POST" action="<?= e(url('/admin/asistencias/crear')) ?>" id="form-asistencia" novalidate>
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px;">
        <div class="af-grid af-grid--3">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Categoría Deportiva</label>
                <select id="sel-cat" name="categoria_id" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= (int) $c['categoria_id'] ?>" <?= (int)old('categoria_id') === (int)$c['categoria_id'] ? 'selected' : '' ?>><?= e($c['nombre_categoria']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Fecha del Evento</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e(old('fecha_evento', date('Y-m-d'))) ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Tipo de Actividad</label>
                <select name="tipo_evento" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach (TIPO_EVENTO as $op): ?>
                        <option value="<?= e($op) ?>" <?= old('tipo_evento') === $op ? 'selected' : '' ?>><?= e($op) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e(old('ubicacion', 'Cancha UPTP')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Clima</label>
                <select name="clima" class="form-control">
                    <option value="">— Seleccione —</option>
                    <?php foreach (CLIMA_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= old('clima') !== '' && (int)old('clima') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" required value="<?= e(old('hora_inicio')) ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" required value="<?= e(old('hora_fin')) ?>">
            </div>
        </div>

        <div class="form-group" style="margin-top: 16px;">
            <label class="form-label"><span class="required">*</span> Entrenador a Cargo</label>
            <select name="entrenador_id" class="form-control" required>
                <option value="">— Seleccione —</option>
                <?php foreach ($entrenadores as $e): ?>
                    <option value="<?= (int) $e['usuario_id'] ?>" <?= (int)old('entrenador_id') === (int)$e['usuario_id'] ? 'selected' : '' ?>><?= e($e['nombre'] . ' ' . $e['apellido']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div id="atletas-container" style="display: none;">
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-2);">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
                <div id="stats-asistencia" style="font-size: 13px; font-weight: 600; color: var(--color-primary);">
                    Cargando atletas...
                </div>
            </div>
            <div id="atletas-list-wrap"></div>
        </div>

        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 24px; gap: 12px;">
            <button type="reset" class="btn btn-ghost">Cancelar</button>
            <button type="submit" class="btn btn-primary btn-lg" id="btn-save" style="padding: 12px 32px;">
                <i class="ph ph-check-circle"></i> Guardar Asistencia
            </button>
            <button type="button" class="btn-help" id="btn-help-asistencia" title="¿Cómo registrar asistencia?" style="width: 44px; height: 44px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </div>

    <div id="no-atletas" class="card" style="display: none; text-align: center; padding: 48px;">
        <i class="ph ph-user-minus" style="font-size: 48px; opacity: 0.2;"></i>
        <p style="margin-top: 16px; color: var(--color-text-muted);">No hay atletas registrados en esta categoría.</p>
    </div>
</form>

<style>
.asistencia-row {
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 24px;
    padding: 16px 24px;
    border-bottom: 1px solid var(--color-border);
    align-items: center;
    transition: background 0.2s;
}
.asistencia-row:hover { background: var(--color-bg-alt); }
.asistencia-row:last-child { border-bottom: 0; }

.status-options {
    display: flex;
    background: var(--color-surface-2);
    padding: 4px;
    border-radius: 8px;
    gap: 4px;
}
.status-btn {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    border: 0;
    background: transparent;
    color: var(--color-text-muted);
    transition: all 0.2s;
}
.status-btn.active[data-val="Presente"] { background: var(--color-success); color: #fff; }
.status-btn.active[data-val="Ausente"] { background: var(--color-danger); color: #fff; }
.status-btn.active[data-val="Justificado"] { background: var(--color-warning); color: #fff; }

.obs-input {
    width: 250px;
    border-radius: 8px;
    font-size: 13px;
}
</style>

<script>
(function () {
    const $cat = document.getElementById('sel-cat');
    const $container = document.getElementById('atletas-container');
    const $noAtletas = document.getElementById('no-atletas');
    const $listWrap = document.getElementById('atletas-list-wrap');
    const $stats = document.getElementById('stats-asistencia');

    const oldAtletas = <?= json_encode(old('atletas') ?? []) ?>;
    const oldEstatus = <?= json_encode(old('estatus') ?? []) ?>;
    const oldObservaciones = <?= json_encode(old('observaciones') ?? []) ?>;

    const escapeHtml = (str) => String(str).replace(/"/g, '&quot;').replace(/'/g, '&#39;');

    $cat.addEventListener('change', async () => {
        const id = $cat.value;
        if (!id) {
            $container.style.display = 'none';
            $noAtletas.style.display = 'none';
            return;
        }

        try {
            $stats.textContent = 'Cargando...';
            const atletas = await API.get(`<?= e(url('/api/asistencias/categoria')) ?>/${id}`);
            
            if (!atletas || !atletas.length) {
                $container.style.display = 'none';
                $noAtletas.style.display = 'block';
                return;
            }

            $noAtletas.style.display = 'none';
            $container.style.display = 'block';
            $stats.textContent = `${atletas.length} Atletas encontrados`;

            $listWrap.innerHTML = atletas.map(a => {
                const athleteIdStr = String(a.atleta_id);
                const isOld = oldAtletas.includes(athleteIdStr) || oldAtletas.includes(a.atleta_id);
                const currentStatus = isOld && oldEstatus[athleteIdStr] ? oldEstatus[athleteIdStr] : 'Presente';
                const currentObs = isOld && oldObservaciones[athleteIdStr] ? oldObservaciones[athleteIdStr] : '';

                return `
                <div class="asistencia-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                            ${a.nombre[0]}${a.apellido[0]}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--color-text);">${a.nombre} ${a.apellido}</div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">C.I: ${a.cedula || '—'}</div>
                        </div>
                    </div>
                    
                    <div class="status-options" data-atleta="${a.atleta_id}">
                        <input type="hidden" name="estatus[${a.atleta_id}]" value="${currentStatus}" class="status-val">
                        <button type="button" class="status-btn ${currentStatus === 'Presente' ? 'active' : ''}" data-val="Presente">Presente</button>
                        <button type="button" class="status-btn ${currentStatus === 'Ausente' ? 'active' : ''}" data-val="Ausente">Ausente</button>
                        <button type="button" class="status-btn ${currentStatus === 'Justificado' ? 'active' : ''}" data-val="Justificado">Justificado</button>
                    </div>

                    <div>
                        <input type="text" name="observaciones[${a.atleta_id}]" class="form-control obs-input" placeholder="Observación opcional..." value="${escapeHtml(currentObs)}">
                    </div>
                    
                    <input type="hidden" name="atletas[]" value="${a.atleta_id}">
                </div>
                `;
            }).join('');

            // Lógica de botones de estado
            $listWrap.querySelectorAll('.status-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const wrap = this.parentElement;
                    wrap.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    wrap.querySelector('.status-val').value = this.dataset.val;
                });
            });

        } catch (e) {
            console.error(e);
            CadaModal.alert({ title: 'Error', text: 'No se pudo cargar la lista de atletas.', type: 'danger' });
        }
    });

    // Botón de ayuda [?]
    document.getElementById('btn-help-asistencia')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Guía: Registro de Asistencia',
            '<?= e(asset("img/ayuda/formulario_asistencia.png")) ?>'
        );
    });

    // Validación estándar al submit con custom validation para hora de inicio/fin
    FormValidator.init('#form-asistencia', {
        custom: function(form) {
            const hInicio = form.querySelector('[name="hora_inicio"]');
            const hFin = form.querySelector('[name="hora_fin"]');
            if (hInicio.value && hFin.value && hInicio.value >= hFin.value) {
                return [
                    {
                        element: hInicio,
                        label: 'La hora de inicio debe ser menor a la hora de fin.'
                    },
                    {
                        element: hFin,
                        label: 'La hora de fin debe ser mayor a la hora de inicio.'
                    }
                ];
            }
            return [];
        }
    });

    document.getElementById('form-asistencia').addEventListener('submit', function(e) {
        // FormValidator preventDefault() e.stopImmediatePropagation() si falla.
        // Si no falla, este listener corre y pone el spinner.
        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Guardando...';
    });

    // Si hay una categoría seleccionada previamente (por old()), disparar el cambio después de que carguen todos los scripts y la API esté disponible
    function autoTrigger() {
        if (typeof API !== 'undefined') {
            if ($cat.value) {
                $cat.dispatchEvent(new Event('change'));
            }
        } else {
            setTimeout(autoTrigger, 50);
        }
    }
    if (document.readyState === 'complete') {
        autoTrigger();
    } else {
        window.addEventListener('load', autoTrigger);
    }
})();
</script>
