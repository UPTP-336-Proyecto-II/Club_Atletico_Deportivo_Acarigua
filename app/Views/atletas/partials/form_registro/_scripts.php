<script>
// —— Tabs & Navegación ————————————————————————————————————————————————————————
const tabs = document.querySelectorAll('.ft-tab');
const panels = document.querySelectorAll('.form-tab-panel');
const btnNext = document.getElementById('btn-next');
const btnPrev = document.getElementById('btn-prev');
const btnSubmit = document.getElementById('btn-submit');
const stepNumEl = document.getElementById('current-step-num');

let currentIdx = 0;

function updateUI() {
    const isLast = currentIdx === tabs.length - 1;
    const isFirst = currentIdx === 0;

    // Actualizar Tabs y Paneles
    tabs.forEach((tab, i) => {
        tab.classList.toggle('active', i === currentIdx);
        panels[i].classList.toggle('active', i === currentIdx);
    });

    // Actualizar Botones
    btnPrev.style.display = isFirst ? 'none' : 'inline-flex';
    btnNext.style.display = isLast ? 'none' : 'inline-flex';
    btnSubmit.style.display = isLast ? 'inline-flex' : 'none';
    
    // Actualizar Contador
    if (stepNumEl) stepNumEl.textContent = currentIdx + 1;
}

// validarCedula definida más abajo con soporte para Partida (P)

function showError(id, msg) {
    const wrap = document.getElementById('phone-wrap-' + id);
    if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    const inp = document.getElementById(id);
    if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
}

function validateStep(idx) {
    try {
        const panel = panels[idx];
        const requiredInputs = panel.querySelectorAll('[required]');
        let isValid = true;
        let missingFields = [];
        
        requiredInputs.forEach(input => {
            if (input.style.display === 'none' || input.offsetParent === null) return;
            const fg = input.closest('.form-group');
            const labelEl = fg ? fg.querySelector('.form-label') : null;
            const label = labelEl ? labelEl.textContent.replace('*', '').trim() : (input.name || 'Campo');
            
            const wrap = input.closest('.phone-field');
            if (!input.value.trim()) {
                if (wrap) wrap.style.borderColor = 'var(--color-danger)';
                else input.style.borderColor = 'var(--color-danger)';
                missingFields.push(label);
                isValid = false;
            } else {
                if (wrap) wrap.style.borderColor = '';
                else input.style.borderColor = '';
            }
        });

        // Validaciones especiales por step
        if (idx === 0) { // Personal
            const ced = document.getElementById('cedula');
            const cedPref = document.getElementById('cedula_prefix');
            const cedInput = document.getElementById('cedula_number');
            
            if (cedPref && cedPref.value === 'P') {
                const y = document.getElementById('folio_year').value;
                const a = document.getElementById('folio_acta').value;
                const n = document.getElementById('folio_num').value;
                if (!y || !a || !n) {
                    showError('cedula', 'Completa el Código de Partida');
                    missingFields.push('Código de Partida');
                    isValid = false;
                } else if (ced && ced.value && !validarCedula(ced.value)) {
                    showError('cedula', 'Formato de Código de Partida inválido (Año-Acta-Folio)');
                    missingFields.push('Código de Partida (Formato)');
                    isValid = false;
                }
            } else {
                if (ced && ced.value && !validarCedula(ced.value)) {
                    showError('cedula', 'Formato de cédula inválido');
                    missingFields.push('Cédula (Formato)');
                    isValid = false;
                }
            }

            const telEl = document.getElementById('telefono_number');
            if (telEl && telEl.value && telEl.value.length !== 7) {
                showError('telefono', 'Teléfono debe tener 7 dígitos');
                missingFields.push('Teléfono Personal (Formato)');
                isValid = false;
            }
        }
        
        if (idx === 2) { // Representante
            const tced = document.getElementById('tutor_cedula');
            if (tced && tced.value && !validarCedula(tced.value)) {
                showError('tutor_cedula', 'Formato inválido');
                missingFields.push('Cédula del Representante (Formato)');
                isValid = false;
            }
            
            const ttel = document.getElementById('tutor_telefono_number');
            if (ttel && ttel.value && ttel.value.length !== 7) {
                showError('tutor_telefono', 'Teléfono debe tener 7 dígitos');
                missingFields.push('Teléfono del Representante (Formato)');
                isValid = false;
            }
        }

        if (!isValid) {
            if (typeof CadaModal !== 'undefined' && CadaModal.alert) {
                CadaModal.alert({
                    title: 'Campos Requeridos',
                    text: 'Debes completar los siguientes campos: <br><br><strong>' + missingFields.join(', ') + '</strong>',
                    type: 'warning'
                });
            }
        }

        return isValid;
    } catch (err) {
        console.error("Error en validateStep:", err);
        CadaModal.alert({ title: 'Error Interno', text: 'Ocurrió un error al validar: ' + err.message, type: 'danger' });
        return false;
    }
}

// Click en botones
btnNext.addEventListener('click', () => {
    if (validateStep(currentIdx)) {
        if (currentIdx < tabs.length - 1) {
            currentIdx++;
            updateUI();
        }
    }
});

btnPrev.addEventListener('click', () => {
    if (currentIdx > 0) {
        currentIdx--;
        updateUI();
    }
});

// Evitar envío del formulario si el paso actual (ej. paso 3) no es válido
const mainForm = document.querySelector('.af-card');
if (mainForm) {
    mainForm.addEventListener('submit', (e) => {
        if (!validateStep(currentIdx)) {
            e.preventDefault();
        }
    });
}


// --- Lógica de cascada para direcciones ---
const selEstado = document.getElementById('sel-estado');
const selMunicipio = document.getElementById('sel-municipio');
const selParroquia = document.getElementById('sel-parroquia');

function loadEstados() {
    // Asumimos 232 = Venezuela
    fetch('<?= e(url('/api/direcciones/estados/232')) ?>')
        .then(res => res.json())
        .then(data => {
            selEstado.innerHTML = '<option value="">Selecciona Estado...</option>';
            data.forEach(est => {
                let selected = (parseInt(selEstado.dataset.current) === est.estado_id) ? 'selected' : '';
                selEstado.innerHTML += `<option value="${est.estado_id}" ${selected}>${est.estado}</option>`;
            });
            if (selEstado.value) loadMunicipios(selEstado.value);
        })
        .catch(console.error);
}

function loadMunicipios(estadoId) {
    if (!estadoId) {
        selMunicipio.innerHTML = '<option value="">Selecciona Municipio...</option>';
        selMunicipio.disabled = true;
        selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
        selParroquia.disabled = true;
        return;
    }
    selMunicipio.disabled = false;
    fetch('<?= e(url('/api/direcciones/municipios')) ?>/' + estadoId)
        .then(res => res.json())
        .then(data => {
            selMunicipio.innerHTML = '<option value="">Selecciona Municipio...</option>';
            data.forEach(mun => {
                let selected = (parseInt(selMunicipio.dataset.current) === parseInt(mun.municipio_id)) ? 'selected' : '';
                selMunicipio.innerHTML += `<option value="${mun.municipio_id}" ${selected}>${mun.municipio}</option>`;
            });
            if (selMunicipio.value) loadParroquias(selMunicipio.value);
        })
        .catch(console.error);
}

function loadParroquias(municipioId) {
    if (!municipioId) {
        selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
        selParroquia.disabled = true;
        return;
    }
    selParroquia.disabled = false;
    fetch('<?= e(url('/api/direcciones/parroquias')) ?>/' + municipioId)
        .then(res => res.json())
        .then(data => {
            selParroquia.innerHTML = '<option value="">Selecciona Parroquia...</option>';
            data.forEach(par => {
                let selected = (parseInt(selParroquia.dataset.current) === parseInt(par.parroquia_id)) ? 'selected' : '';
                selParroquia.innerHTML += `<option value="${par.parroquia_id}" ${selected}>${par.parroquia}</option>`;
            });
        })
        .catch(console.error);
}

selEstado.addEventListener('change', (e) => {
    selMunicipio.dataset.current = '0';
    selParroquia.dataset.current = '0';
    loadMunicipios(e.target.value);
});
selMunicipio.addEventListener('change', (e) => {
    selParroquia.dataset.current = '0';
    loadParroquias(e.target.value);
});

// Inicializar cascada de direcciones
loadEstados();


// Botón Limpiar
const btnReset = document.getElementById('btn-reset');
const form = document.querySelector('.af-card');

if (btnReset) {
    btnReset.addEventListener('click', () => {
        if (typeof CadaModal !== 'undefined') {
            CadaModal.confirm({
                title: 'Limpiar Formulario',
                text: '¿Estás seguro de que deseas limpiar todo el formulario y restablecer todos los campos?',
                type: 'warning',
                confirmText: 'Sí, limpiar'
            }).then(confirmed => {
                if (confirmed) {
                    // Reset campos nativos
                    form.reset();
                    
                    // Reset widgets custom
                    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.phone-field').forEach(el => el.style.borderColor = '');
                    
                    // Reset Foto
                    if (fotoPreviewCont) {
                        fotoPreviewCont.style.display = 'none';
                        fotoPreviewImg.src = '';
                        fotoLabel.classList.remove('has-file');
                        fotoLabel.querySelector('span').textContent = 'Subir foto';
                    }
                    
                    // Volver al inicio
                    currentIdx = 0;
                    updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        } else {
            CadaModal.confirm({
                title: '¿Limpiar Formulario?',
                text: '¿Estás seguro de que deseas limpiar todo el formulario? Se perderán los datos ingresados.',
                type: 'warning',
                confirmText: 'Sí, Limpiar',
                cancelText: 'Cancelar'
            }).then(confirmed => {
                if (confirmed) {
                    // Reset campos nativos
                    form.reset();
                    
                    // Reset widgets custom
                    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');
                    document.querySelectorAll('.phone-field').forEach(el => el.style.borderColor = '');
                    
                    // Reset Foto
                    if (fotoPreviewCont) {
                        fotoPreviewCont.style.display = 'none';
                        fotoPreviewImg.src = '';
                        fotoLabel.classList.remove('has-file');
                        fotoLabel.querySelector('span').textContent = 'Subir foto';
                    }
                    
                    // Volver al inicio
                    currentIdx = 0;
                    updateUI();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }
    });
}

// Inicializar
updateUI();

// —— Cédula y Partida de Nacimiento ——————————————————————————————————————————————
const CEDULA_REGEX = /^[VE]-\d{1,3}(\.\d{3})+$/;
const PARTIDA_REGEX = /^P-\d{4}-[A-Z0-9]{1,5}-[A-Z0-9]{1,5}$/;

function formatCedulaNumber(val) {
    let digits = val.replace(/\D/g, '').substring(0, 8);
    return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function validarCedula(val) {
    if (!val) return true;
    if (val.startsWith('P-')) {
        return PARTIDA_REGEX.test(val);
    }
    const digitsOnly = val.replace(/\D/g, '');
    if (digitsOnly.length < 7 || digitsOnly.length > 9) {
        return false;
    }
    return CEDULA_REGEX.test(val);
}

function showError(id, msg) {
    const wrap = document.getElementById('phone-wrap-' + id);
    if (wrap) wrap.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
    const inp = document.getElementById(id);
    if (inp && !wrap) inp.style.borderColor = msg ? 'var(--color-danger,#e53e3e)' : '';
}
function clearError(id) { showError(id, ''); }

function setupCedulaWidget(prefixId, numberId, hiddenId, errorKey) {
    const prefixEl = document.getElementById(prefixId);
    const numberEl = document.getElementById(numberId);
    const hiddenEl = document.getElementById(hiddenId);
    if (!prefixEl || !numberEl || !hiddenEl) return;

    // Folio elements (solo para el widget del atleta, si existen)
    const folioInputs = document.getElementById('folio_inputs');
    const fYear = document.getElementById('folio_year');
    const fActa = document.getElementById('folio_acta');
    const fNum = document.getElementById('folio_num');

    function sync() {
        let val = '';
        if (prefixEl.value === 'P' && folioInputs) {
            let y = fYear.value.replace(/\D/g, '').substring(0, 4);
            let a = fActa.value.replace(/[^a-zA-Z0-9]/g, '').substring(0, 5).toUpperCase();
            let n = fNum.value.replace(/[^a-zA-Z0-9]/g, '').substring(0, 5).toUpperCase();
            fYear.value = y; fActa.value = a; fNum.value = n;
            val = (y||a||n) ? `${y}-${a}-${n}` : '';
        } else {
            val = numberEl.value.trim().toUpperCase();
            if (prefixEl.value === 'V' || prefixEl.value === 'E') {
                val = formatCedulaNumber(val);
            }
            numberEl.value = val;
        }
        hiddenEl.value = val.length ? prefixEl.value + '-' + val : '';
    }
    
    // Si ya viene un valor cargado (no Folio, ya que PHP lo maneja en los inputs de Folio)
    if (hiddenEl.value) {
        let raw = hiddenEl.value;
        let prefix = 'V', num = raw;
        if (raw.includes('-')) {
            let parts = raw.split('-');
            prefix = parts[0];
            if (prefix === 'P' && parts.length > 2) {
                num = parts.slice(1).join('-');
            } else {
                num = parts[1] || '';
            }
        } else {
            let firstChar = raw.charAt(0).toUpperCase();
            if (['V', 'E', 'P'].includes(firstChar)) {
                prefix = firstChar;
                num = raw.substring(1);
            }
        }
        prefixEl.value = prefix;
        if (prefix !== 'P') {
            numberEl.value = prefix === 'V' || prefix === 'E' ? formatCedulaNumber(num) : num;
        }
    }
    sync();

    numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
    if (folioInputs) {
        fYear.addEventListener('input', () => { sync(); clearError(errorKey); });
        fActa.addEventListener('input', () => { sync(); clearError(errorKey); });
        fNum.addEventListener('input', () => { sync(); clearError(errorKey); });
    }

    prefixEl.addEventListener('change', () => {
        if (prefixEl.value === 'P') {
            numberEl.style.display = 'none';
            if (folioInputs) {
                folioInputs.style.display = 'flex';
                fYear.focus();
            } else {
                numberEl.style.display = 'block';
                numberEl.placeholder = "Cód. Partida";
                numberEl.maxLength = 15;
                numberEl.focus();
            }
        } else {
            if (folioInputs) folioInputs.style.display = 'none';
            numberEl.style.display = 'block';
            numberEl.placeholder = "12.345.678";
            numberEl.maxLength = 10;
            numberEl.focus();
        }
        sync();
        clearError(errorKey);
    });
    
    const blurHandler = () => {
        const val = hiddenEl.value;
        if (val && !validarCedula(val)) {
            showError(errorKey, prefixEl.value === 'P' ? 'Completa Año, Acta y Cód. Partida' : 'Formato inválido. Ej: ' + prefixEl.value + '-12.345.678');
        } else {
            clearError(errorKey);
        }
    };

    numberEl.addEventListener('blur', blurHandler);
    if (folioInputs) {
        fYear.addEventListener('blur', blurHandler);
        fActa.addEventListener('blur', blurHandler);
        fNum.addEventListener('blur', blurHandler);
    }
    
    numberEl.addEventListener('focus', () => clearError(errorKey));
    if (folioInputs) {
        fYear.addEventListener('focus', () => clearError(errorKey));
        fActa.addEventListener('focus', () => clearError(errorKey));
        fNum.addEventListener('focus', () => clearError(errorKey));
    }
}

// Inicializar widgets de Cédula
setupCedulaWidget('cedula_prefix', 'cedula_number', 'cedula', 'cedula');
setupCedulaWidget('tutor_cedula_prefix', 'tutor_cedula_number', 'tutor_cedula', 'tutor_cedula');

// —— Preview de Foto ——————————————————————————————————————————————————————————
const fotoInput = document.getElementById('foto-input');
const fotoLabel = document.getElementById('foto-label');
const fotoPreviewCont = document.getElementById('foto-preview-container');
const fotoPreviewImg = document.getElementById('foto-preview-img');
const btnRemoveFoto = document.getElementById('btn-remove-foto');

if (fotoInput) {
    fotoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fotoPreviewImg.src = e.target.result;
                fotoPreviewCont.style.display = 'block';
                fotoLabel.classList.add('has-file');
                fotoLabel.querySelector('span').textContent = 'Cambiar foto';
            }
            reader.readAsDataURL(file);

            // Eliminar flag de borrado si se sube una nueva
            let deleteInput = document.getElementById('delete-foto-flag');
            if (deleteInput) deleteInput.remove();
        }
    });

    btnRemoveFoto.addEventListener('click', (e) => {
        e.preventDefault();
        fotoInput.value = '';
        fotoPreviewCont.style.display = 'none';
        fotoLabel.classList.remove('has-file');
        fotoLabel.querySelector('span').textContent = 'Subir foto';

        // Notificar al servidor que se borre la foto si es edición
        let deleteInput = document.getElementById('delete-foto-flag');
        if (!deleteInput) {
            deleteInput = document.createElement('input');
            deleteInput.type = 'hidden';
            deleteInput.name = 'eliminar_foto';
            deleteInput.id = 'delete-foto-flag';
            deleteInput.value = '1';
            fotoInput.parentNode.appendChild(deleteInput);
        }
    });
}

// —— Widget teléfono ——————————————————————————————————————————————————————————
function setupPhoneWidget(prefixId, numberId, hiddenId, errorKey) {
    const prefixEl = document.getElementById(prefixId);
    const numberEl = document.getElementById(numberId);
    const hiddenEl = document.getElementById(hiddenId);
    if (!prefixEl || !numberEl || !hiddenEl) return;

    function sync() {
        const num = numberEl.value.replace(/[^\d]/g, '').substring(0, 7);
        numberEl.value = num;
        hiddenEl.value = num.length ? prefixEl.value + num : '';
    }
    sync();

    numberEl.addEventListener('input', () => { sync(); clearError(errorKey); });
    prefixEl.addEventListener('change', () => { sync(); clearError(errorKey); numberEl.focus(); });
    numberEl.addEventListener('blur', () => {
        const num = numberEl.value;
        if (num && num.length !== 7) showError(errorKey, 'Ingresa 7 dígitos');
        else clearError(errorKey);
    });
    numberEl.addEventListener('focus', () => clearError(errorKey));
}

setupPhoneWidget('telefono_prefix',     'telefono_number',     'telefono',     'telefono');
setupPhoneWidget('tutor_telefono_prefix', 'tutor_telefono_number', 'tutor_telefono', 'tutor_telefono');

// --- Lógica Dinámica de Cédula y Representante por Edad ---
function updateDynamicRequirements() {
    const dobInput = document.querySelector('input[name="fecha_nacimiento"]');
    if (!dobInput || !dobInput.value) return;

    const dob = new Date(dobInput.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;

    // 1. Cédula obligatoria si es mayor de 9 años
    const cedInput = document.getElementById('cedula_number');
    const cedLabel = document.getElementById('label-cedula');
    if (age > 9) {
        if (cedInput) cedInput.setAttribute('required', 'true');
        if (cedLabel && !cedLabel.querySelector('.required')) {
            cedLabel.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
        }
    } else {
        if (cedInput) cedInput.removeAttribute('required');
        if (cedLabel) {
            const reqSpan = cedLabel.querySelector('.required');
            if (reqSpan) reqSpan.remove();
        }
    }

    const isAdult = age >= 18;

    // 2. Teléfono personal obligatorio si es mayor de edad (Adulto)
    const telInput = document.getElementById('telefono_number');
    const telLabel = document.getElementById('label-telefono');
    if (isAdult) {
        if (telInput) telInput.setAttribute('required', 'true');
        if (telLabel && !telLabel.querySelector('.required')) {
            telLabel.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
        }
    } else {
        if (telInput) telInput.removeAttribute('required');
        if (telLabel) {
            const reqSpan = telLabel.querySelector('.required');
            if (reqSpan) reqSpan.remove();
        }
    }

    // 3. Datos del representante no obligatorios si es mayor de edad
    const tutorFields = [
        'tutor_nombres',
        'tutor_apellidos',
        'tutor_cedula_number',
        'tutor_telefono_number',
        'tutor_relacion'
    ];

    tutorFields.forEach(id => {
        const inputEl = document.getElementById(id) || document.querySelector(`[name="${id}"]`);
        if (inputEl) {
            const fg = inputEl.closest('.form-group');
            const label = fg ? fg.querySelector('.form-label') : null;
            if (isAdult) {
                inputEl.removeAttribute('required');
                if (label) {
                    const reqSpan = label.querySelector('.required');
                    if (reqSpan) reqSpan.remove();
                }
            } else {
                inputEl.setAttribute('required', 'true');
                if (label && !label.querySelector('.required')) {
                    label.insertAdjacentHTML('afterbegin', '<span class="required">*</span> ');
                }
            }
        }
    });
}

const dobEl = document.querySelector('input[name="fecha_nacimiento"]');
if (dobEl) {
    dobEl.addEventListener('change', updateDynamicRequirements);
    dobEl.addEventListener('input', updateDynamicRequirements);
    // Ejecutar al cargar para atletas existentes o reediciones
    updateDynamicRequirements();
}

// —— Validación Final ———————————————————————————————————————————————————————
document.querySelector('form').addEventListener('submit', function(e) {
    if (!validateStep(currentIdx)) {
        e.preventDefault();
        return;
    }
    // Si validateStep pasa, el formulario se envía.
});

// —— Botón de Ayuda [?] ——————————————————————————————————————————————————————
document.getElementById('btn-help-atleta')?.addEventListener('click', () => {
    FormValidator.showHelp(
        'Guía: Registro de Atleta',
        '<?= e(asset("img/ayuda/formulario_atleta.png")) ?>'
    );
});
</script>
