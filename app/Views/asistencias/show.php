<?php /** @var array $actividad @var array $detalles */ ?>
<div class="page-header">
    <div>
        <h1>Detalle de Asistencia</h1>
        <div class="subtitle">Sesión del <?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
    </div>
    <div style="display: flex; gap: 12px;">
        <a href="<?= e(url('/admin/asistencias')) ?>" class="btn btn-ghost">
            <i class="ph ph-caret-left"></i> Volver al Listado
        </a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url('/admin/asistencias/' . $actividad['actividad_id'] . '/editar')) ?>" class="btn btn-outline">
                <i class="ph ph-pencil-simple"></i> Editar Registro
            </a>
        <?php endif; ?>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 300px; gap: 24px;">
    <!-- Lista de Asistencia -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid var(--color-border); background: var(--color-surface-2);">
            <h3 style="margin:0; font-size: 16px;"><i class="ph ph-users-three"></i> Lista de Atletas</h3>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="padding-left: 24px;">Atleta</th>
                    <th>Cédula</th>
                    <th>Estado de Asistencia</th>
                    <th style="padding-right: 24px;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $d): ?>
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600;"><?= e($d['nombre'] . ' ' . $d['apellido']) ?></div>
                    </td>
                    <td><?= e($d['cedula'] ?? '—') ?></td>
                    <td>
                        <?php 
                            $status = (int)$d['estatus'] === 1 ? 'Presente' : 'Ausente';
                            $badge = (int)$d['estatus'] === 1 ? 'success' : 'danger';
                        ?>
                        <span class="badge badge-<?= $badge ?>" style="font-weight: 600; text-transform: uppercase; font-size: 11px;">
                            <span style="display:inline-block; width:6px; height:6px; border-radius:50%; background:currentColor; margin-right:6px;"></span>
                            <?= e($status) ?>
                        </span>
                    </td>
                    <td style="padding-right: 24px;">
                        <span style="font-size: 13px; color: var(--color-text-muted);">
                            <?= e($d['observaciones'] ?? '—') ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Resumen de la Sesión -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <div class="card">
            <h3 style="margin-top: 0; font-size: 15px;"><i class="ph ph-info"></i> Información General</h3>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 20px;">
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Tipo de Actividad</label>
                    <?php 
                        $tipoLabel = match ((int)($actividad['tipo_actividad'] ?? 1)) {
                            0 => 'Partido',
                            1 => 'Entrenamiento',
                            3 => 'Evento Especial',
                            default => 'General'
                        };
                    ?>
                    <div style="font-weight: 600; color: var(--color-primary);"><?= e($tipoLabel) ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Entrenador Responsable</label>
                    <div style="font-weight: 500;"><?= e($actividad['entrenador'] ?? 'No definido') ?></div>
                </div>
                <div>
                    <label style="display: block; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--color-text-muted); margin-bottom: 4px;">Fecha de Registro</label>
                    <div style="font-weight: 500;"><?= e(date('d/m/Y', strtotime($actividad['fecha']))) ?></div>
                </div>
            </div>
        </div>

        <div class="card" style="background: var(--color-primary); color: #fff;">
            <h3 style="margin-top: 0; font-size: 15px; color: #fff;">Estadísticas</h3>
            <?php 
                $total = count($detalles);
                $presentes = count(array_filter($detalles, fn($x) => (int)$x['estatus'] === 1));
                $porcentaje = $total > 0 ? round(($presentes / $total) * 100) : 0;
            ?>
            <div style="text-align: center; padding: 20px 0;">
                <div style="font-size: 48px; font-weight: 800;"><?= $porcentaje ?>%</div>
                <div style="font-size: 13px; opacity: 0.9;">Asistencia Total</div>
            </div>
            <div style="display: flex; justify-content: space-between; font-size: 13px; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 12px;">
                <span>Presentes: <strong><?= $presentes ?></strong></span>
                <span>Total: <strong><?= $total ?></strong></span>
            </div>
        </div>
    </div>
</div>
