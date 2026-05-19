<?php
/** @var array|null $item @var array $entrenadores @var string $action */
$c = $item ?? [];
$get = fn(string $k, $d = '') => old($k, $c[$k] ?? $d);
$isEdit = !empty($c['categoria_id']);
?>

<div class="page-header">
    <div>
        <?php if ($isEdit): ?>
            <!-- Elemento oculto intencionalmente -->
        <?php endif; ?>
        <h1><?= $isEdit ? 'Editar' : 'Crear' ?> Categoría</h1>
        <div class="subtitle"><?= $isEdit ? 'Modifica los parámetros de la categoría' : 'Define un nuevo grupo por rango de edad' ?></div>
    </div>
    <div style="display: flex; gap: 12px; align-items: center;">
        <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost"><i class="ph ph-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="card" style="max-width: 800px; margin: 0 auto; padding: 0; overflow: hidden;">
    <div style="padding: 24px; background: var(--color-surface); border-bottom: 1px solid var(--color-border);">
        <h3 style="margin: 0; font-size: 18px; display: flex; align-items: center; gap: 10px;">
            <i class="ph ph-info" style="color: var(--color-primary)"></i> Información General
        </h3>
    </div>

    <form id="form-categoria" method="POST" action="<?= e($action) ?>" style="padding: 32px;" novalidate>
        <?= csrf_field() ?>
        
        <div class="form-group">
            <label class="form-label"><span class="required">*</span> Nombre de la Categoría</label>
            <div style="position: relative;">
                <i class="ph ph-tag" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                <input type="text" name="nombre_categoria" class="form-control" style="padding-left: 40px;" 
                       placeholder="Ej: Sub-12, Semillitas..." required maxlength="50" 
                       value="<?= e($get('nombre_categoria', '')) ?>">
            </div>

        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label"><span class="required">*</span> Edad Mínima</label>
                <div style="position: relative;">
                    <i class="ph ph-user-circle" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                    <input type="number" name="edad_min" min="6" max="100" class="form-control" style="padding-left: 40px;" 
                           required value="<?= e($get('edad_min', 6)) ?>">
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label"><span class="required">*</span> Edad Máxima</label>
                <div style="position: relative;">
                    <i class="ph ph-user-circle-plus" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted);"></i>
                    <input type="number" name="edad_max" min="6" max="100" class="form-control" style="padding-left: 40px;" 
                           required value="<?= e($get('edad_max', 18)) ?>">
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; margin-bottom: 24px;">
            <div class="form-group" style="margin: 0;">
                <label class="form-label"><span class="required">*</span> Género</label>
                <div style="position: relative;">
                    <i class="ph ph-gender-intersex" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="sexo_categoria" class="form-control" style="padding-left: 40px;" required>
                        <option value="">— Seleccione —</option>
                        <option value="M" <?= $get('sexo_categoria', '') === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $get('sexo_categoria', '') === 'F' ? 'selected' : '' ?>>Femenino</option>
                        <option value="X" <?= $get('sexo_categoria', '') === 'X' ? 'selected' : '' ?>>Mixto</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label"><span class="required">*</span> Entrenador Responsable</label>
                <div style="position: relative;">
                    <i class="ph ph-user-gear" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="usuario_id" class="form-control" style="padding-left: 40px;" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($entrenadores as $e): ?>
                            <option value="<?= (int) $e['usuario_id'] ?>" <?= (int) $get('usuario_id', '') === (int) $e['usuario_id'] ? 'selected' : '' ?>>
                                <?= e($e['nombre'] . ' ' . $e['apellido']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin: 0;">
                <label class="form-label">Estado</label>
                <div style="position: relative;">
                    <i class="ph ph-toggle-left" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--color-text-muted); z-index: 10;"></i>
                    <select name="estatus" class="form-control" style="padding-left: 40px;">
                        <?php foreach (['activa','inactiva'] as $op): ?>
                            <option value="<?= e($op) ?>" <?= strtolower($get('estatus', 'activa')) === $op ? 'selected' : '' ?>>
                                <?= e(ucfirst($op)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div style="background: var(--color-surface); margin: 32px -32px -32px; padding: 24px 32px; border-top: 1px solid var(--color-border); display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
            <a href="<?= e(url('/admin/categorias')) ?>" class="btn btn-ghost">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg" style="padding-left: 40px; padding-right: 40px;">
                <i class="ph ph-floppy-disk"></i> <?= $isEdit ? 'Guardar Cambios' : 'Crear Categoría' ?>
            </button>
            <button type="button" class="btn-help" id="btn-help-categoria" title="¿Cómo llenar este formulario?" style="width: 44px; height: 44px;">
                <i class="ph ph-question"></i>
            </button>
        </div>
    </form>
</div>

<style>
/* Solucionar visibilidad de las flechitas de input number en modo oscuro */
html.dark input[type="number"] {
    color-scheme: dark;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botón de ayuda [?]
    document.getElementById('btn-help-categoria')?.addEventListener('click', () => {
        FormValidator.showHelp(
            'Guía: Registro de Categoría',
            '<?= e(asset("img/ayuda/formulario_categoria.png")) ?>'
        );
    });

    // Validación estándar al submit
    FormValidator.init('#form-categoria', {
        custom: (form) => {
            const errors = [];
            const min = parseInt(form.querySelector('input[name="edad_min"]')?.value);
            const max = parseInt(form.querySelector('input[name="edad_max"]')?.value);
            if (!isNaN(min) && !isNaN(max) && min > max) {
                errors.push({ label: 'La edad mínima no puede ser mayor a la máxima', element: form.querySelector('input[name="edad_min"]') });
            }
            return errors;
        }
    });
});
</script>
