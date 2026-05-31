<?php /** @var array $categoria @var array $atletas */ ?>
<div class="page-header">
    <div>
        <h1><?= e($categoria['nombre_categoria']) ?></h1>
        <div class="subtitle">Detalles de la categoría e historial de atletas asignados</div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">
            <i class="ph ph-arrow-left"></i> Volver
        </a>
        <?php if (can('admin')): ?>
            <a href="<?= e(url('/admin/categorias/' . $categoria['categoria_id'] . '/asignar')) ?>" class="btn btn-primary">
                <i class="ph ph-user-plus"></i> Asignar Atletas
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Tarjetas de Información Rápida -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= count($atletas) ?></div>
        <div class="stat-label">Atletas Asignados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number">
            <i class="ph ph-gender-<?= strtolower($categoria['sexo_categoria']) === 'f' ? 'female' : (strtolower($categoria['sexo_categoria']) === 'm' ? 'male' : 'intersex') ?>" style="font-size: 28px; vertical-align: middle;"></i>
            <?= $categoria['sexo_categoria'] === 'F' ? 'Femenino' : ($categoria['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto') ?>
        </div>
        <div class="stat-label">Género de Categoría</div>
    </div>
    <div class="stat-card">
        <div class="stat-number"><?= (int)$categoria['edad_min'] ?> - <?= (int)$categoria['edad_max'] ?></div>
        <div class="stat-label">Rango de Edad (Años)</div>
    </div>
</div>

<div class="data-table-wrap card" style="padding: 0; overflow: hidden;">
    <table class="data-table" style="margin: 0; border: none;">
        <thead style="background: var(--color-bg-alt);">
            <tr>
                <th style="width:52px; padding-left: 24px;"></th>
                <th>Atleta</th>
                <th>Cédula / Código</th>
                <th>Dorsal</th>
                <th>Posición Principal</th>
                <th>Posición Secundaria</th>
                <th>Estatus</th>
                <th style="width:160px; text-align: right; padding-right: 24px;">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($atletas)): ?>
            <tr>
                <td colspan="8" style="padding: 64px 24px; text-align: center;">
                    <i class="ph ph-users-three text-muted" style="font-size: 48px; margin-bottom: 16px; display: block; opacity: 0.5;"></i>
                    <h3 class="text-muted" style="margin: 0 0 8px;">No hay atletas asignados</h3>
                    <p class="text-muted" style="font-size: 14px; max-width: 400px; margin: 0 auto;">Usa el botón de <strong>Asignar Atletas</strong> para inscribir deportistas en este grupo.</p>
                </td>
            </tr>
        <?php else: foreach ($atletas as $a): ?>
            <tr>
                <td style="padding-left: 24px;">
                    <?php if (!empty($a['foto'])): ?>
                        <div style="position: relative; width: 44px; height: 44px; padding: 2px; border: 1px solid var(--color-border); border-radius: 50%; background: var(--color-bg);">
                            <img src="<?= e(url($a['foto'])) ?>" class="avatar-thumb" alt="" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; display: block;">
                        </div>
                    <?php else: ?>
                        <div class="avatar-placeholder" style="width: 44px; height: 44px; border-radius: 50%; background: var(--color-primary-light); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-weight: bold; border: 1px solid var(--color-primary-light);">
                            <?= e(mb_substr($a['nombre'], 0, 1) . mb_substr($a['apellido'], 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="font-weight: 600; color: var(--color-text);"><?= e($a['nombre'] . ' ' . $a['apellido']) ?></div>
                </td>
                <td>
                    <div style="font-size: 13px; color: var(--color-text-muted);"><?= !empty($a['cedula']) ? e($a['cedula']) : 'Sin Cédula' ?></div>
                </td>
                <td>
                    <span class="badge badge-outline" style="font-size: 14px; font-weight: 700; padding: 4px 10px;">
                        <?= $a['nun_dorsal'] !== null ? '#' . (int)$a['nun_dorsal'] : 'S/D' ?>
                    </span>
                </td>
                <td>
                    <span style="font-size: 14px; color: var(--color-text-muted);">
                        <?= e($a['posicion_principal'] ?? 'No definida') ?>
                    </span>
                </td>
                <td>
                    <span style="font-size: 14px; color: var(--color-text-muted);">
                        <?= e($a['posicion_secundaria'] ?? 'Ninguna') ?>
                    </span>
                </td>
                <td>
                    <?php if ((int)($a['estatus'] ?? 1) === 1): ?>
                        <span class="badge badge-success" style="font-weight: 600; font-size: 12px; padding: 4px 10px; border-radius: 12px;">Vigente</span>
                    <?php else: ?>
                        <span class="badge badge-danger" style="font-weight: 600; font-size: 12px; padding: 4px 10px; border-radius: 12px;">Vencido</span>
                    <?php endif; ?>
                </td>
                <td style="text-align: right; padding-right: 24px;">
                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <a href="<?= e(url('/admin/atletas/' . $a['atleta_id'])) ?>" class="btn btn-sm btn-ghost" title="Ver Perfil Atleta" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="ph ph-eye"></i>
                        </a>
                        <?php if (can('admin')): ?>
                            <a href="<?= e(url('/admin/asig-categorias/' . $a['asignacion_id'] . '/editar')) ?>" class="btn btn-sm btn-ghost" title="Editar Asignación" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <form method="POST" action="<?= e(url('/admin/asig-categorias/' . $a['asignacion_id'] . '/eliminar')) ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="button" class="btn btn-sm btn-ghost text-danger btn-retirar-atleta" title="Retirar de Categoría" data-nombre="<?= e($a['nombre'] . ' ' . $a['apellido']) ?>" style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.btn-retirar-atleta').forEach(btn => {
        btn.addEventListener('click', () => {
            const form = btn.closest('form');
            const nombre = btn.getAttribute('data-nombre');

            CadaModal.confirm({
                title: '¿Retirar de la Categoría?',
                text: `¿Estás seguro de que deseas retirar a <strong>${nombre}</strong> de esta categoría? El atleta no será eliminado del sistema, pero perderá su dorsal y posición en este grupo.`,
                type: 'danger',
                confirmText: 'Sí, Retirar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
