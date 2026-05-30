<?php /** @var array $actividad @var array $detalles @var array $entrenadores */ ?>
<div class="page-header">
    <div>
        <h1>Editar Asistencia</h1>
        <div class="subtitle">Modificando registro del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <a href="<?= e(url('/admin/asistencias/' . $actividad['actividad_id'])) ?>" class="btn btn-ghost">
        <i class="ph ph-caret-left"></i> Volver al Detalle
    </a>
</div>

<form method="POST" action="<?= e(url('/admin/asistencias/' . $actividad['actividad_id'] . '/editar')) ?>" id="form-edit-asistencia">
    <?= csrf_field() ?>

    <div class="card" style="margin-bottom: 24px;">
        <div class="af-grid af-grid--2">
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Fecha del Evento</label>
                <input type="date" name="fecha_evento" class="form-control" required value="<?= e($actividad['fecha']) ?>" min="2019-01-01" max="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label class="form-label"><span class="required">*</span> Tipo de Actividad</label>
                <select name="tipo_evento" class="form-control" required>
                    <?php 
                        $currentTipo = match ((int)$actividad['tipo_actividad']) {
                            0 => 'Partido',
                            1 => 'Entrenamiento',
                            3 => 'Evento especial',
                            default => 'Entrenamiento'
                        };
                    ?>
                    <?php foreach (TIPO_EVENTO as $op): ?>
                        <option value="<?= e($op) ?>" <?= $op === $currentTipo ? 'selected' : '' ?>><?= e($op) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 16px;">
            <div class="form-group">
                <label class="form-label">Ubicación</label>
                <input type="text" name="ubicacion" class="form-control" placeholder="Cancha UPTP" value="<?= e($actividad['ubicacion'] ?? 'Cancha UPTP') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Clima</label>
                <select name="clima" class="form-control">
                    <option value="">Selecciona...</option>
                    <?php foreach (CLIMA_TIPO as $k => $v): ?>
                        <option value="<?= $k ?>" <?= (isset($actividad['clima']) && (int)$actividad['clima'] === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" value="<?= e($actividad['hora_inicio'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" value="<?= e($actividad['hora_fin'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group" style="margin-top: 16px;">
            <label class="form-label"><span class="required">*</span> Entrenador Responsable</label>
            <select name="entrenador_id" class="form-control" required>
                <?php foreach ($entrenadores as $e): ?>
                    <option value="<?= (int) $e['usuario_id'] ?>" <?= (int)$e['usuario_id'] === (int)$actividad['usuario_id'] ? 'selected' : '' ?>>
                        <?= e($e['nombre'] . ' ' . $e['apellido']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2);">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
        </div>
        
        <div id="atletas-list-wrap">
            <?php foreach ($detalles as $d): ?>
                <div class="asistencia-row">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                            <?= e($d['nombre'][0] . $d['apellido'][0]) ?>
                        </div>
                        <div>
                            <div style="font-weight: 600; color: var(--color-text);"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                            <div style="font-size: 12px; color: var(--color-text-muted);">C.I: <?= e($d['cedula'] ?? '—') ?></div>
                        </div>
                    </div>
                    
                    <div class="status-options" data-atleta="<?= (int)$d['atleta_id'] ?>">
                        <?php $currentStatus = match ((int)$d['estatus']) { 1 => 'Presente', 2 => 'Justificado', default => 'Ausente' }; ?>
                        <input type="hidden" name="estatus[<?= (int)$d['atleta_id'] ?>]" value="<?= $currentStatus ?>" class="status-val">
                        <button type="button" class="status-btn <?= $currentStatus === 'Presente' ? 'active' : '' ?>" data-val="Presente">Presente</button>
                        <button type="button" class="status-btn <?= $currentStatus === 'Ausente' ? 'active' : '' ?>" data-val="Ausente">Ausente</button>
                        <button type="button" class="status-btn <?= $currentStatus === 'Justificado' ? 'active' : '' ?>" data-val="Justificado">Justificado</button>
                    </div>

                    <div>
                        <input type="text" name="observaciones[<?= (int)$d['atleta_id'] ?>]" class="form-control obs-input" placeholder="Observación..." value="<?= e($d['observaciones'] ?? '') ?>">
                    </div>
                    
                    <input type="hidden" name="atletas[]" value="<?= (int)$d['atleta_id'] ?>">
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 24px; gap: 12px;">
        <button type="button" onclick="history.back()" class="btn btn-ghost">Descartar Cambios</button>
        <button type="submit" class="btn btn-primary btn-lg" id="btn-save" style="padding: 12px 32px;">
            <i class="ph ph-floppy-disk"></i> Guardar Cambios
        </button>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const wrap = this.parentElement;
            wrap.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            wrap.querySelector('.status-val').value = this.dataset.val;
        });
    });

    document.getElementById('form-edit-asistencia').addEventListener('submit', function(e) {
        const hInicio = document.querySelector('[name="hora_inicio"]').value;
        const hFin = document.querySelector('[name="hora_fin"]').value;
        if (hInicio && hFin && hInicio >= hFin) {
            e.preventDefault();
            CadaModal.alert({ title: 'Error en Horario', text: 'La hora de inicio debe ser menor a la hora de fin.', type: 'danger' });
            return;
        }

        const btn = document.getElementById('btn-save');
        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-spinner-gap spinning"></i> Actualizando...';
    });
});
</script>
