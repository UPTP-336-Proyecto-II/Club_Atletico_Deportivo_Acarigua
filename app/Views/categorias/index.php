<?php /** @var array $items */ ?>
<div class="page-header">
    <div>
        <h1>Categorías Deportivas</h1>
        <div class="subtitle">Gestión y organización de grupos por rangos de edad</div>
    </div>
    <?php if (can('admin')): ?>
        <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nueva Categoría
        </a>
    <?php endif; ?>
</div>

<?php 
$total = count($items);
$activas = count(array_filter($items, fn($i) => strtolower($i['estatus']) === 'activa'));
$totalAtletas = array_sum(array_column($items, 'total_atletas'));
?>

<!-- Métricas de Categorías -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number"><?= $total ?></div>
        <div class="stat-label">Total Categorías</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-success)"><?= $activas ?></div>
        <div class="stat-label">Activas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary)"><?= $totalAtletas ?></div>
        <div class="stat-label">Atletas Totales</div>
    </div>
</div>

<div class="quick-grid" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
    <?php if (empty($items)): ?>
        <div class="card" style="grid-column: 1 / -1; text-align: center; padding: 80px 24px; background: var(--color-surface);">
            <div style="width: 80px; height: 80px; background: var(--color-surface-2); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <i class="ph ph-shield-slash" style="font-size: 40px; color: var(--color-text-muted);"></i>
            </div>
            <h3 style="margin-bottom: 8px;">No hay categorías registradas</h3>
            <p class="text-muted" style="max-width: 400px; margin: 0 auto 24px;">Las categorías permiten agrupar a los atletas por edad y asignarles un entrenador específico.</p>
            <a href="<?= e(url('/admin/categorias/crear')) ?>" class="btn btn-outline">
                <i class="ph ph-plus"></i> Crear Primera Categoría
            </a>
        </div>
    <?php else: foreach ($items as $c): ?>
        <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;">
            <!-- Header Card -->
            <div style="padding: 24px; border-bottom: 1px solid var(--color-border); position: relative;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                            <span class="badge badge-<?= strtolower($c['estatus']) === 'activa' ? 'success' : 'warning' ?>">
                                <?= e(ucfirst($c['estatus'])) ?>
                            </span>
                            <span style="font-size: 12px; color: var(--color-text-muted); font-weight: 600;">ID: #<?= $c['categoria_id'] ?></span>
                        </div>
                        <h2 style="margin: 0; font-family: var(--font-display); font-size: 22px; font-weight: 700; color: var(--color-text);">
                            <?= e($c['nombre_categoria']) ?>
                        </h2>
                        <div style="display: flex; align-items: center; gap: 10px; color: var(--color-text-muted); font-size: 13px; font-weight: 500; margin-top: 4px;">
                            <span><i class="ph ph-users"></i> <?= (int) $c['edad_min'] ?> a <?= (int) $c['edad_max'] ?> años</span>
                            <span style="width: 4px; height: 4px; background: var(--color-border); border-radius: 50%;"></span>
                            <span>
                                <i class="ph ph-gender-<?= strtolower($c['sexo_categoria'] ?? 'M') === 'f' ? 'female' : (strtolower($c['sexo_categoria'] ?? 'M') === 'm' ? 'male' : 'intersex') ?>"></i>
                                <?= $c['sexo_categoria'] === 'F' ? 'Femenino' : ($c['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto') ?>
                            </span>
                        </div>
                    </div>
                    <?php if (can('admin')): ?>
                        <div class="flex gap-sm">
                            <a href="<?= e(url("/admin/categorias/{$c['categoria_id']}/editar")) ?>" class="btn btn-ghost btn-sm" title="Editar">
                                <i class="ph ph-pencil-simple"></i>
                            </a>
                            <form method="POST" action="<?= e(url("/admin/categorias/{$c['categoria_id']}/eliminar")) ?>" data-confirm="¿Está seguro de eliminar esta categoría?" style="display:inline;">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-ghost btn-sm text-danger" title="Eliminar">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Info Section -->
            <div style="padding: 24px; flex: 1; display: flex; flex-direction: column; gap: 20px;">
                <!-- Entrenador -->
                <div style="background: var(--color-surface); padding: 12px 16px; border-radius: var(--radius-sm); border: 1px solid var(--color-border);">
                    <div style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 8px;">Entrenador Responsable</div>
                    <?php if (!empty($c['entrenador'])): ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 32px; height: 32px; background: var(--color-primary); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800;">
                                <?= e(mb_substr($c['entrenador'], 0, 1)) ?>
                            </div>
                            <div style="font-weight: 600; font-size: 14px; color: var(--color-text);"><?= e($c['entrenador']) ?></div>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 12px; color: var(--color-text-muted);">
                            <div style="width: 32px; height: 32px; background: var(--color-surface-2); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                <i class="ph ph-user-minus" style="font-size: 14px;"></i>
                            </div>
                            <div style="font-size: 13px; font-style: italic;">Sin asignar</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Capacidad / Atletas -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 8px;">
                        <div style="font-size: 13px; font-weight: 600; color: var(--color-text);">Atletas Inscritos</div>
                        <div style="font-size: 16px; font-weight: 800; color: var(--color-primary);"><?= (int) ($c['total_atletas'] ?? 0) ?></div>
                    </div>
                    <?php 
                        $maxRef = 30; // Referencia visual
                        $porcentaje = min(100, ((int) ($c['total_atletas'] ?? 0) / $maxRef) * 100);
                        $barColor = $porcentaje > 80 ? 'var(--color-danger)' : ($porcentaje > 50 ? 'var(--color-warning)' : 'var(--color-success)');
                    ?>
                    <div style="height: 8px; background: var(--color-surface-2); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: <?= $porcentaje ?>%; background: <?= $barColor ?>; border-radius: 4px; transition: width 0.5s ease;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 6px; font-size: 11px; color: var(--color-text-muted); font-weight: 500;">
                        <span>0 Atletas</span>
                        <span>Capacidad sugerida: <?= $maxRef ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div style="padding: 16px 24px; background: var(--color-surface); border-top: 1px solid var(--color-border); display: flex; gap: 12px;">
                <a href="<?= e(url('/admin/atletas?categoria_id=' . $c['categoria_id'])) ?>" class="btn btn-primary" style="flex: 1; font-size: 14px;">
                    <i class="ph ph-users"></i> Listar Atletas
                </a>
                <a href="<?= e(url('/admin/reportes/asistencia?categoria_id=' . $c['categoria_id'])) ?>" class="btn btn-ghost" title="Asistencias">
                    <i class="ph ph-calendar-check"></i>
                </a>
            </div>
        </div>
    <?php endforeach; endif; ?>
</div>

<style>
.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
    border-color: var(--color-primary-light);
}
</style>
