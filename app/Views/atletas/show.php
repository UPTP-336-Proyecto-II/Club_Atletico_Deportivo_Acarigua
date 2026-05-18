<?php /** @var array $atleta */ ?>
<style>
    /* Estilos para pestañas con scroll elegante */
    .profile-tabs {
        display: flex;
        flex-wrap: nowrap;
        /* Mantener en una sola línea */
        overflow-x: auto;
        /* Habilitar scroll horizontal */
        width: 100%;
        padding: 0 24px !important;
        background: var(--color-bg-alt);
        border-bottom: 1px solid var(--color-border);
        -webkit-overflow-scrolling: touch;
        /* Desplazamiento suave en móviles */
    }

    @supports not selector(::-webkit-scrollbar) {
        .profile-tabs {
            scrollbar-width: thin;
            scrollbar-color: #BE123C transparent;
        }
    }


    .profile-tabs .tab-btn {
        flex: 0 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 16px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        border-radius: 0;
        /* Se quita la línea de abajo completamente para todos los estados */
        border-bottom: none !important;
        transition: all 0.2s ease;
        color: var(--color-text-muted);
    }

    .profile-tabs .tab-btn.active {
        color: var(--color-primary) !important;
    }

    .profile-tabs .tab-btn i {
        font-size: 18px;
    }
</style>
<div class="page-header">
    <div>
        <h1>Perfil del Atleta</h1>
        <div class="subtitle">Expediente integral y seguimiento deportivo</div>
    </div>
    <div class="flex gap">
        <a href="<?= e(url('/admin/atletas')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Directorio</a>
        <a href="<?= e(url("/admin/reportes/atleta/{$atleta['atleta_id']}")) ?>" class="btn btn-outline"
            target="_blank"><i class="ph ph-file-pdf"></i> Imprimir PDF</a>
    </div>
</div>

<div style="display:grid; grid-template-columns:300px 1fr; gap:24px;" class="show-layout">
    <!-- Panel Izquierdo (Resumen) -->
    <div style="display:flex; flex-direction:column; gap:24px;">
        <div class="card" style="text-align:center; padding-top: 32px; position: relative;">
            <?php if (can('admin')): ?>
                <button type="button" class="btn-icon-premium" id="btn-abrir-editar-basico" title="Editar Datos Básicos">
                    <i class="ph ph-pencil-simple"></i>
                </button>
            <?php endif; ?>
            <div style="position: relative; width: 180px; height: 180px; margin: 0 auto 20px; cursor: pointer; group"
                id="btn-abrir-editar-foto" title="Cambiar Foto">
                <div
                    style="position: absolute; inset: -5px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, #ff4d4d 100%); opacity: 0.15; filter: blur(8px);">
                </div>
                <?php if (!empty($atleta['foto'])): ?>
                    <div style="position: relative; width: 100%; height: 100%; border-radius: 50%; padding: 4px; background: var(--color-bg); border: 2px solid var(--color-border); box-shadow: var(--shadow-lg); transition: transform 0.2s;"
                        class="hover-scale">
                        <img src="<?= e(url($atleta['foto'])) ?>"
                            style="width:100%; height:100%; border-radius:50%; object-fit:cover; display: block;">
                        <div style="position: absolute; inset: 4px; border-radius: 50%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: white; opacity: 0; transition: opacity 0.2s;"
                            class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="avatar-placeholder"
                        style="width:100%; height:100%; font-size:48px; background: var(--color-primary-light); color: var(--color-primary); border: 4px solid var(--color-bg); box-shadow: var(--shadow-md); position: relative;">
                        <?= e(mb_substr($atleta['nombre'], 0, 1) . mb_substr($atleta['apellido'], 0, 1)) ?>
                        <div style="position: absolute; inset: 0; border-radius: 50%; background: rgba(0,0,0,0.1); display: flex; align-items: center; justify-content: center; color: var(--color-primary); opacity: 0; transition: opacity 0.2s;"
                            class="photo-overlay">
                            <i class="ph ph-camera" style="font-size: 32px;"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <h2 style="margin:0 0 4px; font-family: var(--font-display);">
                <?= e($atleta['nombre'] . ' ' . $atleta['apellido']) ?></h2>
            <div style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 16px;">C.I:
                <?= !empty($atleta['cedula']) ? e($atleta['cedula']) : 'Sin Cédula' ?></div>

            <?php
            $estatusVal = (int) ($atleta['estatus'] ?? 1);
            $badge = match ($estatusVal) {
                1 => 'success', // Activo
                2 => 'warning', // Lesionado
                0 => 'danger',  // Suspendido
                3 => 'outline', // Inactivo
                default => 'primary'
            };
            $label = match ($estatusVal) {
                1 => 'Activo',
                2 => 'Lesionado',
                0 => 'Suspendido',
                3 => 'Inactivo',
                default => 'Desconocido'
            };
            ?>
            <span class="badge badge-<?= $badge ?>" style="padding: 6px 16px; border-radius: 20px; font-weight: 600;">
                <span
                    style="display:inline-block; width:8px; height:8px; border-radius:50%; background:currentColor; margin-right:6px;"></span>
                <?= e($label) ?>
            </span>

            <hr style="border:none; border-top:1px solid var(--color-border); margin: 24px 0;">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; text-align: left;">
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Categoría</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-shield-chevron text-muted"></i>
                        <?= e($atleta['nombre_categoria'] ?? 'Sin asignar') ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Posición</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-t-shirt text-muted"></i> <?= e($atleta['nombre_posicion'] ?? 'No definida') ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Edad</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-calendar-blank text-muted"></i>
                        <?php
                        $nac = new DateTime($atleta['fecha_nac'] ?? 'today');
                        $hoy = new DateTime();
                        echo $hoy->diff($nac)->y . ' años';
                        ?>
                    </div>
                </div>
                <div>
                    <div
                        style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; font-weight: 600;">
                        Pierna Dominante</div>
                    <div style="font-weight: 500; display:flex; align-items:center; gap:4px; margin-top:4px;">
                        <i class="ph ph-sneaker text-muted"></i>
                        <?= !empty($atleta['pierna_dominante']) ? e(ucfirst($atleta['pierna_dominante'])) : 'Sin definir' ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div
                style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--color-border); padding-bottom: 12px; margin-bottom: 16px;">
                <h3 style="margin:0; font-size: 16px;"><i class="ph ph-phone-call"></i> Contacto</h3>
            </div>
            <div style="margin-top: 0;">
                <div style="display:flex; align-items:center; gap: 12px; margin-bottom: 12px;">
                    <div
                        style="width:36px; height:36px; border-radius:8px; background:var(--color-bg-alt); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                        <i class="ph ph-whatsapp-logo" style="font-size:20px;"></i></div>
                    <div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Teléfono Personal</div>
                        <div style="font-weight: 500;">
                            <?= !empty($atleta['telefono']) ? e($atleta['telefono']) : 'No registrado' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Derecho (Contenido Principal con Tabs) -->
    <div class="card" style="padding: 0; overflow: hidden; display: flex; flex-direction: column;">
        <div class="profile-tabs">
            <button class="tab-btn active" data-target="tab-general"><i class="ph ph-user-list"></i> Datos
                Generales</button>
            <button class="tab-btn" data-target="tab-ficha"><i class="ph ph-heartbeat"></i> Ficha Médica</button>
            <button class="tab-btn" data-target="tab-antropometria"><i class="ph ph-ruler"></i> Antropometría</button>
            <button class="tab-btn" data-target="tab-pruebas"><i class="ph ph-chart-line-up"></i> Pruebas
                Físicas</button>
            <button class="tab-btn" data-target="tab-asistencia"><i class="ph ph-calendar-check"></i>
                Asistencia</button>
        </div>

        <div style="padding: 32px; flex: 1;">


            <!-- Tab: General -->
            <?php include __DIR__ . '/partials/_tab_general.php'; ?>

            <!-- Tab: Ficha Médica -->
            <?php include __DIR__ . '/partials/_tab_ficha_medica.php'; ?>

            <!-- Tab: Antropometría -->
            <?php include __DIR__ . '/partials/_tab_antropometria.php'; ?>

            <!-- Tab: Pruebas Físicas -->
            <?php include __DIR__ . '/partials/_tab_pruebas.php'; ?>

            <!-- Tab: Asistencia -->
            <?php include __DIR__ . '/partials/_tab_asistencia.php'; ?>
        </div>

        <?php include __DIR__ . '/partials/_modals.php'; ?>

    </div>
</div>
</div>

<?php include __DIR__ . '/partials/_styles.php'; ?>

<?php include __DIR__ . '/partials/_scripts.php'; ?>
