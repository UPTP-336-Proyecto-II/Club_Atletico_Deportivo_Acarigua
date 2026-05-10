<?php /** @var array $eventos @var array $pag @var array $filters */ ?>
<div class="page-header">
    <div>
        <h1>Control de Asistencia</h1>
        <div class="subtitle">Gestión y registro de asistencia por categoría</div>
    </div>
    <a href="<?= e(url('/admin/asistencias/crear')) ?>" class="btn btn-primary">
        <i class="ph ph-plus-circle"></i> Registrar Asistencia
    </a>
</div>

<div class="data-table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th style="padding-left: 24px;">Fecha</th>
                <th>Tipo de Evento</th>
                <th>Entrenador Responsable</th>
                <th>Asistencia</th>
                <th style="text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($eventos as $ev): ?>
                <tr>
                    <td style="padding-left: 24px;">
                        <div style="font-weight: 600;"><?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?></div>
                    </td>
                    <td>
                        <?php
                        $label = TIPO_ACTIVIDAD[(int) ($ev['tipo_evento'] ?? 1)] ?? 'General';
                        ?>
                        <span class="badge badge-primary"
                            style="text-transform: uppercase; font-size: 11px;"><?= e($label) ?></span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <i class="ph ph-user-circle text-muted" style="font-size: 20px;"></i>
                            <?= e($ev['entrenador'] ?? 'No definido') ?>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div
                                style="width: 100px; height: 8px; background: var(--color-surface-2); border-radius: 4px; overflow: hidden;">
                                <div
                                    style="width: <?= ($ev['total'] > 0) ? ($ev['presentes'] / $ev['total'] * 100) : 0 ?>%; height: 100%; background: var(--color-success);">
                                </div>
                            </div>
                            <span style="font-size: 13px; font-weight: 500;">
                                <?= (int) $ev['presentes'] ?> / <?= (int) $ev['total'] ?>
                            </span>
                        </div>
                    </td>
                    <td style="text-align: right; padding-right: 24px;">
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <a href="<?= e(url('/admin/asistencias/' . $ev['evento_id'])) ?>" class="btn btn-sm btn-ghost"
                                title="Ver Detalles">
                                <i class="ph ph-eye"></i>
                            </a>
                            <a href="<?= e(url('/admin/asistencias/' . $ev['evento_id'] . '/editar')) ?>"
                                class="btn btn-sm btn-outline" title="Editar">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <form action="<?= e(url('/admin/asistencias/' . $ev['evento_id'] . '/eliminar')) ?>"
                                method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-sm btn-outline btn-delete-asistencia"
                                    title="Eliminar Registro"
                                    data-date="<?= e(date('d/m/Y', strtotime($ev['fecha_evento']))) ?>">
                                    <i class="ph ph-trash" style="color: var(--color-danger);"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($eventos)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 48px;">
                        <i class="ph ph-calendar-x"
                            style="font-size: 48px; opacity: 0.3; display: block; margin-bottom: 12px;"></i>
                        No hay registros de asistencia para mostrar.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.btn-delete-asistencia').forEach(btn => {
            btn.addEventListener('click', function () {
                const form = this.closest('form');
                const date = this.getAttribute('data-date');

                CadaModal.confirm({
                    title: '¿Eliminar Asistencia?',
                    text: `¿Estás seguro de eliminar el registro del día ${date}? Esta acción no se puede deshacer.`,
                    type: 'danger',
                    confirmText: 'Sí, Eliminar',
                    cancelText: 'Cancelar'
                }).then((confirmed) => {
                    if (confirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>