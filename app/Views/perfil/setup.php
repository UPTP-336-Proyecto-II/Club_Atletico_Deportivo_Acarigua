<?php /** @var array $preguntas */ ?>
<div class="login-page">
    <div class="login-card" style="max-width: 500px;">
        <div class="login-card__brand">
            <div class="brand">
                <div class="brand__logo">CADA</div>
                <div class="brand__text" style="text-align:left">
                    <div class="title">Configuración Inicial</div>
                    <div class="subtitle">Seguridad de la Cuenta</div>
                </div>
            </div>
        </div>

        <p class="login-card__subtitle" style="margin-bottom: 24px;">
            Por seguridad, es obligatorio que cambies tu contraseña predeterminada y configures tus preguntas de recuperación antes de acceder al sistema.
        </p>

        <?php include view_path('partials.flash'); ?>

        <form id="form-setup" method="POST" action="<?= e(url('/admin/setup/save')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label class="form-label" for="password"><span class="required">*</span> Nueva Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required minlength="8" pattern="(?=.*[A-Za-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}" title="Debe tener al menos 8 caracteres, una letra, un número y un símbolo especial">
                <ul class="pwd-rules" style="list-style: none; padding: 0; margin: 8px 0 0 0; font-size: 13px; color: var(--color-text-muted);">
                    <li id="rule-len" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Mínimo 8 caracteres</li>
                    <li id="rule-let" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos una letra</li>
                    <li id="rule-num" style="margin-bottom: 4px;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un número</li>
                    <li id="rule-sym" style="margin-bottom: 0;"><i class="ph ph-x-circle" style="color: var(--color-danger); margin-right: 4px;"></i> Al menos un símbolo especial</li>
                </ul>
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirm"><span class="required">*</span> Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required minlength="8">
            </div>

            <hr style="border:0; border-top:1px solid var(--border-color); margin: 24px 0;">

            <div class="form-group">
                <label class="form-label" for="pregunta_1"><span class="required">*</span> Pregunta de Seguridad 1</label>
                <select id="pregunta_1" name="pregunta_1" class="form-control" required>
                    <option value="">Selecciona una pregunta...</option>
                    <?php foreach ($preguntas as $p): ?>
                        <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="respuesta_1"><span class="required">*</span> Respuesta 1</label>
                <input type="text" id="respuesta_1" name="respuesta_1" class="form-control" required autocomplete="off">
            </div>

            <div class="form-group">
                <label class="form-label" for="pregunta_2"><span class="required">*</span> Pregunta de Seguridad 2</label>
                <select id="pregunta_2" name="pregunta_2" class="form-control" required>
                    <option value="">Selecciona una pregunta...</option>
                    <?php foreach ($preguntas as $p): ?>
                        <option value="<?= (int) $p['pregunta_id'] ?>"><?= e($p['preguntas']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="respuesta_2"><span class="required">*</span> Respuesta 2</label>
                <input type="text" id="respuesta_2" name="respuesta_2" class="form-control" required autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary" style="margin-top: 16px; width: 100%;">Guardar y Entrar al Sistema</button>

            <div style="margin-top: 20px; text-align: center; border-top: 1px solid var(--color-border, #eee); padding-top: 16px;">
                <a href="<?= e(url('/logout')) ?>" style="color: var(--color-text-muted); text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="ph ph-arrow-left"></i> Volver al inicio y cerrar sesión
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const p1 = document.getElementById('pregunta_1');
    const p2 = document.getElementById('pregunta_2');

    function checkSelects() {
        const v1 = p1.value;
        const v2 = p2.value;

        // Reset all options
        Array.from(p1.options).forEach(opt => opt.disabled = false);
        Array.from(p2.options).forEach(opt => opt.disabled = false);

        if (v1) {
            const opt2 = p2.querySelector(`option[value="${v1}"]`);
            if (opt2) opt2.disabled = true;
        }
        if (v2) {
            const opt1 = p1.querySelector(`option[value="${v2}"]`);
            if (opt1) opt1.disabled = true;
        }
    }

    p1.addEventListener('change', checkSelects);
    p2.addEventListener('change', checkSelects);

    // Password rules checklist (visual guide, not error messages)
    const pass = document.getElementById('password');
    
    function updateRules() {
        const val = pass.value;
        const rules = [
            { id: 'rule-len', valid: val.length >= 8 },
            { id: 'rule-let', valid: /[A-Za-z]/.test(val) },
            { id: 'rule-num', valid: /[0-9]/.test(val) },
            { id: 'rule-sym', valid: /[^A-Za-z0-9]/.test(val) }
        ];
        
        rules.forEach(r => {
            const li = document.getElementById(r.id);
            if (!li) return;
            const icon = li.querySelector('i');
            if (r.valid) {
                icon.className = 'ph ph-check-circle';
                icon.style.color = 'var(--color-success, #48bb78)';
                li.style.color = 'var(--color-success, #48bb78)';
            } else {
                icon.className = 'ph ph-x-circle';
                icon.style.color = 'var(--color-danger, #e53e3e)';
                li.style.color = 'var(--color-text-muted, #718096)';
            }
        });
    }

    pass.addEventListener('input', updateRules);

    // Standard validation on submit
    FormValidator.init('#form-setup', {
        custom: (form) => {
            const errors = [];
            const passVal = document.getElementById('password')?.value;
            const passConf = document.getElementById('password_confirm')?.value;
            
            if (passVal !== passConf) {
                errors.push({ label: 'Las contrase\u00f1as no coinciden', element: document.getElementById('password_confirm') });
            }
            
            const resp1 = document.getElementById('respuesta_1')?.value;
            if (resp1 && resp1.length < 3) {
                errors.push({ label: 'La respuesta 1 debe tener al menos 3 caracteres', element: document.getElementById('respuesta_1') });
            }
            const resp2 = document.getElementById('respuesta_2')?.value;
            if (resp2 && resp2.length < 3) {
                errors.push({ label: 'La respuesta 2 debe tener al menos 3 caracteres', element: document.getElementById('respuesta_2') });
            }
            
            if (p1.value && p2.value && p1.value === p2.value) {
                errors.push({ label: 'Las preguntas de seguridad deben ser diferentes', element: p2 });
            }
            return errors;
        }
    });
});
</script>
