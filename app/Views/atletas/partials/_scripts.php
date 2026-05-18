<!-- Inclusión de ECharts para gráficos -->
<script src="<?= e(url('/assets/js/lib/echarts.min.js')) ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Manejo de Pestañas
        const tabs = document.querySelectorAll('.tab-btn');
        const contents = document.querySelectorAll('.tab-content');

        // Manejo de Modales (Genérico para cerrar)
        document.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal-overlay');
                if (modal) modal.style.display = 'none';
            });
        });

        // Abrir Modal de Asistencia
        const btnHistAsist = document.getElementById('btn-historial-asistencia');
        const modalHistAsist = document.getElementById('modal-historial-asistencia');
        btnHistAsist?.addEventListener('click', () => {
            if (modalHistAsist) modalHistAsist.style.display = 'flex';
        });

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.style.display = 'none');

                tab.classList.add('active');
                const targetId = tab.getAttribute('data-target');
                document.getElementById(targetId).style.display = 'block';

                // Redimensionar gráficos si están en la pestaña activa
                if (targetId === 'tab-antropometria' && chartAntro) {
                    setTimeout(() => chartAntro.resize(), 50);
                }
                if (targetId === 'tab-pruebas' && chartRadar) {
                    setTimeout(() => chartRadar.resize(), 50);
                }
                if (targetId === 'tab-asistencia' && typeof chartDona !== 'undefined' && chartDona) {
                    setTimeout(() => chartDona.resize(), 50);
                }
            });
        });

        // Activar pestaña desde URL si existe (ej: ?tab=tab-ficha)
        const urlParams = new URLSearchParams(window.location.search);
        const tabParam = urlParams.get('tab');
        if (tabParam) {
            const targetTabBtn = document.querySelector(`.tab-btn[data-target="${tabParam}"]`);
            if (targetTabBtn) {
                targetTabBtn.click();
            }
        }

        // 1.5 Modal de Ficha Médica
        const modalFicha = document.getElementById('modal-ficha-medica');
        function abrirModalFicha() {
            if (modalFicha) modalFicha.style.display = 'flex';
        }
        function cerrarModalFicha() {
            if (modalFicha) modalFicha.style.display = 'none';
        }

        // Botón "Editar" en la ficha (cuando ya hay datos)
        document.getElementById('btn-editar-ficha')?.addEventListener('click', abrirModalFicha);
        // Botón "Registrar Ficha Médica" (cuando no hay datos)
        document.getElementById('btn-crear-ficha')?.addEventListener('click', abrirModalFicha);

        // Cerrar modal con botones de cancelar/cerrar
        modalFicha?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalFicha);
        });
        // Cerrar modal al hacer clic fuera del contenido
        modalFicha?.addEventListener('click', (e) => {
            if (e.target === modalFicha) cerrarModalFicha();
        });

        // 1.6 Modal de Discapacidades
        const modalDisc = document.getElementById('modal-discapacidad');
        const formDisc = document.getElementById('form-discapacidad');
        const baseActionDisc = "<?= e(url("/admin/ficha-medica/{$atleta['atleta_id']}/discapacidad")) ?>";

        function abrirModalDisc(modo = 'agregar', data = {}) {
            if (!modalDisc) return;

            const title = document.getElementById('title-discapacidad');
            const submitText = document.getElementById('submit-text-discapacidad');

            if (modo === 'editar') {
                title.textContent = 'Editar Discapacidad';
                submitText.textContent = 'Guardar';
                formDisc.action = baseActionDisc + '/' + data.id + '/editar';
                document.getElementById('input-tipo-disc').value = data.tipo;
                document.getElementById('input-carnet-disc').value = data.carnet;
                document.getElementById('input-porcentaje-disc').value = data.porcentaje;
            } else {
                title.textContent = 'Agregar Discapacidad';
                submitText.textContent = 'Agregar';
                formDisc.action = baseActionDisc;
                formDisc.reset();
            }

            document.getElementById('discapacidad-error').style.display = 'none';
            modalDisc.style.display = 'flex';
        }

        function cerrarModalDisc() {
            if (modalDisc) modalDisc.style.display = 'none';
        }

        // Interceptar envío del formulario con AJAX
        formDisc?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('discapacidad-error');
            const submitBtn = formDisc.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            try {
                const formData = new FormData(formDisc);
                const response = await fetch(formDisc.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Éxito: recargar para ver la tabla actualizada en la misma pestaña
                    window.location.href = window.location.pathname + '?tab=tab-ficha';
                } else {
                    // Error: mostrar mensaje en el modal
                    errorDiv.textContent = result.message || 'Ocurrió un error inesperado.';
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión. Intente nuevamente.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        document.getElementById('btn-agregar-discapacidad')?.addEventListener('click', () => abrirModalDisc('agregar'));

        document.querySelectorAll('.btn-editar-discapacidad').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const btnEl = e.currentTarget;
                abrirModalDisc('editar', {
                    id: btnEl.getAttribute('data-id'),
                    tipo: btnEl.getAttribute('data-tipo'),
                    carnet: btnEl.getAttribute('data-carnet'),
                    porcentaje: btnEl.getAttribute('data-porcentaje')
                });
            });
        });

        document.querySelectorAll('.btn-delete-disc').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const form = e.currentTarget.closest('form');
                if (typeof CadaModal !== 'undefined') {
                    CadaModal.confirm({
                        title: 'Eliminar Discapacidad',
                        text: '¿Estás seguro de que deseas eliminar esta discapacidad?',
                        type: 'danger',
                        confirmText: 'Sí, eliminar'
                    }).then(confirmed => {
                        if (confirmed) form.submit();
                    });
                } else {
                    if (confirm('¿Eliminar esta discapacidad?')) form.submit();
                }
            });
        });

        const modalBasico = document.getElementById('modal-editar-basico');
        function abrirModalBasico() {
            if (modalBasico) modalBasico.style.display = 'flex';
        }
        function cerrarModalBasico() {
            if (modalBasico) modalBasico.style.display = 'none';
        }
        document.getElementById('btn-abrir-editar-basico')?.addEventListener('click', abrirModalBasico);
        modalBasico?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalBasico);
        });
        modalBasico?.addEventListener('click', (e) => {
            if (e.target === modalBasico) cerrarModalBasico();
        });

        modalDisc?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalDisc);
        });
        modalDisc?.addEventListener('click', (e) => {
            if (e.target === modalDisc) cerrarModalDisc();
        });

        // 1.7 Modal de Mediciones Antropométricas
        const modalMedicion = document.getElementById('modal-medicion');
        const formMedicion = document.getElementById('form-medicion');

        function abrirModalMedicion() {
            if (modalMedicion) {
                document.getElementById('medicion-error').style.display = 'none';
                modalMedicion.style.display = 'flex';
            }
        }

        function cerrarModalMedicion() {
            if (modalMedicion) modalMedicion.style.display = 'none';
        }

        document.getElementById('btn-nueva-medicion')?.addEventListener('click', abrirModalMedicion);

        formMedicion?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('medicion-error');
            const submitBtn = formMedicion.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            try {
                const formData = new FormData(formMedicion);
                const response = await fetch(formMedicion.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = window.location.pathname + '?tab=tab-antropometria';
                } else {
                    errorDiv.textContent = result.message || 'Error al guardar la medición.';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0][0];
                        errorDiv.textContent += ' ' + firstError;
                    }
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        modalMedicion?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalMedicion);
        });

        // 1.7.5 Modal de Edición de Mediciones Antropométricas
        const modalMedicionEditar = document.getElementById('modal-medicion-editar');
        const formMedicionEditar = document.getElementById('form-medicion-editar');

        function cerrarModalMedicionEditar() {
            if (modalMedicionEditar) modalMedicionEditar.style.display = 'none';
        }

        document.querySelectorAll('.btn-editar-medicion').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const fecha = btn.getAttribute('data-fecha');
                const peso = btn.getAttribute('data-peso');
                const altura = btn.getAttribute('data-altura');
                const grasa = btn.getAttribute('data-grasa');
                const musculo = btn.getAttribute('data-musculo');
                const envergadura = btn.getAttribute('data-envergadura');
                const pierna = btn.getAttribute('data-pierna');
                const torso = btn.getAttribute('data-torso');

                // Llenar campos
                // fecha puede venir como '2026-05-17 00:00:00', el input date requiere YYYY-MM-DD
                document.getElementById('edit-fecha_medicion').value = fecha ? fecha.substring(0, 10) : '';
                document.getElementById('edit-peso').value = peso || '';
                document.getElementById('edit-altura').value = altura || '';
                document.getElementById('edit-porcentaje_grasa').value = grasa || '';
                document.getElementById('edit-porcentaje_musculatura').value = musculo || '';
                document.getElementById('edit-envergadura').value = envergadura || '';
                document.getElementById('edit-largo_de_pierna').value = pierna || '';
                document.getElementById('edit-largo_de_torso').value = torso || '';

                // Ajustar acción de form dinámicamente
                formMedicionEditar.action = `<?= url("/admin/medidas") ?>/${id}/editar`;

                document.getElementById('medicion-editar-error').style.display = 'none';
                if (modalMedicionEditar) modalMedicionEditar.style.display = 'flex';
            });
        });

        formMedicionEditar?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('medicion-editar-error');
            const submitBtn = formMedicionEditar.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            try {
                const formData = new FormData(formMedicionEditar);
                const response = await fetch(formMedicionEditar.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = window.location.pathname + '?tab=tab-antropometria';
                } else {
                    errorDiv.textContent = result.message || 'Error al actualizar la medición.';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0][0];
                        errorDiv.textContent += ' ' + firstError;
                    }
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        modalMedicionEditar?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalMedicionEditar);
        });

        // 1.7.6 Eliminación de Mediciones con CadaModal
        document.querySelectorAll('.btn-eliminar-medicion').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const atletaId = "<?= $atleta['atleta_id'] ?>";

                CadaModal.confirm({
                    title: '¿Eliminar Medición?',
                    text: '¿Estás seguro de eliminar este registro antropométrico? Esta acción no se puede deshacer.',
                    type: 'danger',
                    confirmText: 'Sí, Eliminar',
                    cancelText: 'Cancelar'
                }).then((confirmed) => {
                    if (confirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `<?= url('/admin/medidas') ?>/${id}/eliminar?atleta_id=${atletaId}&redirect=${encodeURIComponent(window.location.pathname + '?tab=tab-antropometria')}`;

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_csrf';
                        csrf.value = document.querySelector('meta[name="csrf-token"]').content;

                        form.appendChild(csrf);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });

        // 2. Gráfica Real de Antropometría (Peso vs Altura)
        var chartAntro = null;
        const chartAntroDOM = document.getElementById('chart-antropometria');
        // Dado que el modelo PHP ya devuelve el historial ordenado en ASC (Cronológico), lo usamos directo
        const historialMedidas = <?= json_encode($medidas_historial ?? []) ?>;

        if (chartAntroDOM && typeof echarts !== 'undefined') {
            chartAntro = echarts.init(chartAntroDOM);

            const dates = historialMedidas.map(m => {
                const d = new Date(m.fecha_medicion);
                return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
            });
            const pesos = historialMedidas.map(m => parseFloat(m.peso) || 0);
            const alturas = historialMedidas.map(m => {
                let h = parseFloat(m.altura) || 0;
                if (h > 0 && h < 3) h = h * 100;
                return h;
            });
            const imcs = historialMedidas.map(m => {
                const p = parseFloat(m.peso) || 0;
                const h = parseFloat(m.altura) || 0;
                if (p > 0 && h > 0) {
                    // Si el dato viejo está guardado en metros (ej. 1.75), lo dejamos igual.
                    // Si es en centímetros (ej. 175), lo pasamos a metros dividiendo entre 100.
                    const a = h > 3 ? h / 100 : h;
                    const imc = p / (a * a);
                    return isFinite(imc) ? parseFloat(imc.toFixed(1)) : 0;
                }
                return 0;
            });

            const optionAntro = {
                tooltip: { trigger: 'axis' },
                legend: { 
                    data: ['Peso (kg)', 'Altura (cm)', 'IMC'], 
                    bottom: 0,
                    textStyle: { fontSize: 12 }
                },
                grid: { left: '10%', right: '10%', bottom: '15%', containLabel: true },
                xAxis: { type: 'category', boundaryGap: true, data: dates.length ? dates : ['Sin datos'] },
                yAxis: [
                    { type: 'value', name: 'Kg/Cm', position: 'left', min: 0 },
                    { type: 'value', name: 'IMC', position: 'right', splitLine: { show: false } }
                ],
                series: [
                    {
                        name: 'Peso (kg)',
                        type: 'bar',
                        yAxisIndex: 0,
                        barWidth: '35%',
                        itemStyle: {
                            color: '#F59E0B',
                            borderRadius: [4, 4, 0, 0]
                        },
                        data: pesos.length ? pesos : [0]
                    },
                    {
                        name: 'Altura (cm)',
                        type: 'line',
                        smooth: true,
                        yAxisIndex: 0,
                        lineStyle: { color: '#1E293B', width: 2, type: 'dashed' },
                        itemStyle: { color: '#1E293B' },
                        data: alturas.length ? alturas : [0]
                    },
                    {
                        name: 'IMC',
                        type: 'line',
                        smooth: true,
                        yAxisIndex: 1,
                        lineStyle: { color: '#10B981', width: 3 },
                        itemStyle: { color: '#10B981' },
                        data: imcs.length ? imcs : [0]
                    }
                ]
            };
            chartAntro.setOption(optionAntro);
        }

        // 1.8 Modal de Pruebas Físicas
        const modalPrueba = document.getElementById('modal-prueba');
        const formPrueba = document.getElementById('form-prueba');

        function abrirModalPrueba() {
            if (modalPrueba) {
                document.getElementById('prueba-error').style.display = 'none';
                modalPrueba.style.display = 'flex';
            }
        }

        function cerrarModalPrueba() {
            if (modalPrueba) modalPrueba.style.display = 'none';
        }

        document.getElementById('btn-nueva-prueba')?.addEventListener('click', abrirModalPrueba);

        formPrueba?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('prueba-error');
            const submitBtn = formPrueba.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            try {
                const formData = new FormData(formPrueba);
                const response = await fetch(formPrueba.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const text = await response.text();
                let result;
                try {
                    result = JSON.parse(text);
                } catch (e) {
                    console.error("Invalid JSON:", text);
                    throw new Error("El servidor no devolvió una respuesta válida.");
                }

                if (result.success) {
                    window.location.href = window.location.pathname + '?tab=tab-pruebas';
                } else {
                    errorDiv.textContent = result.message || 'Error al guardar los resultados.';
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                errorDiv.textContent = error.message || 'Error de conexión.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        modalPrueba?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalPrueba);
        });

        // 3. Gráfica Real Radar de Pruebas Físicas (Ya implementada arriba)

        // 4. Lógica de los 4 Nuevos Modales de Edición (Sesiones)
        const formsEdit = [
            { id: 'basico', modal: 'modal-editar-basico', form: 'form-editar-basico', error: 'error-basico', tab: 'tab-general' },
            { id: 'contacto', modal: 'modal-editar-contacto', form: 'form-editar-contacto', error: 'error-contacto', tab: 'tab-general' },
            { id: 'representante', modal: 'modal-editar-representante', form: 'form-editar-representante', error: 'error-representante', tab: 'tab-general' },
            { id: 'direccion', modal: 'modal-editar-direccion', form: 'form-editar-direccion', error: 'error-direccion', tab: 'tab-general' },
            { id: 'foto', modal: 'modal-editar-foto', form: 'form-editar-foto', error: 'error-foto', tab: 'tab-general' }
        ];

        // CSS Dinámico para efectos de foto y botones
        const style = document.createElement('style');
        style.innerHTML = `
        #btn-abrir-editar-foto:hover .photo-overlay { opacity: 1 !important; }
        #btn-abrir-editar-foto:hover .hover-scale { transform: scale(1.02); }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.2); font-size: 14px; line-height: 1.4; }
        
        /* Efectos de botones */
        .btn, .btn-icon, .tab-btn { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer; }
        .btn:active, .btn-icon:active { transform: scale(0.95); }
        
        .btn-icon-premium {
            background: var(--color-bg-alt);
            border: 1px solid var(--color-border);
            color: var(--color-text-muted);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-icon-premium:hover {
            background: var(--color-primary-light);
            color: var(--color-primary);
            border-color: var(--color-primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.15);
        }
        #btn-abrir-editar-basico { position: absolute; top: 12px; right: 12px; }

        /* Zona de Carga Dinámica */
        .upload-zone {
            border: 2px dashed var(--color-border);
            border-radius: 12px;
            padding: 32px 16px;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--color-bg-alt);
            position: relative;
        }
        .upload-zone:hover {
            border-color: var(--color-primary);
            background: var(--color-primary-light);
        }
        .upload-zone.dragover {
            border-color: var(--color-primary);
            background: rgba(37, 99, 235, 0.1);
            transform: scale(1.02);
        }
        .upload-content i { font-size: 40px; color: var(--color-primary); margin-bottom: 8px; display: block; }
        .upload-content p { font-weight: 600; margin: 0; color: var(--color-text); }
        .upload-content span { font-size: 12px; color: var(--color-text-muted); }
        
        /* Animación de Éxito */
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .success-animation { animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
    `;
        document.head.appendChild(style);

        // Lógica de Zona de Carga (Drag & Drop)
        const uploadZone = document.getElementById('upload-zone-foto');
        const fileInput = document.getElementById('input-foto-file');
        const filenameDisplay = document.getElementById('foto-filename');

        uploadZone?.addEventListener('click', () => fileInput.click());
        fileInput?.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                filenameDisplay.textContent = e.target.files[0].name;
                filenameDisplay.style.color = 'var(--color-primary)';
                filenameDisplay.style.fontWeight = '600';
            }
        });
        uploadZone?.addEventListener('dragover', (e) => { e.preventDefault(); uploadZone.classList.add('dragover'); });
        uploadZone?.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
        uploadZone?.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                filenameDisplay.textContent = e.dataTransfer.files[0].name;
                filenameDisplay.style.color = 'var(--color-primary)';
                filenameDisplay.style.fontWeight = '600';
            }
        });

        formsEdit.forEach(item => {
            const modal = document.getElementById(item.modal);
            const form = document.getElementById(item.form);
            const errorDiv = document.getElementById(item.error);
            const btnAbrir = document.getElementById(`btn-abrir-editar-${item.id}`);

            btnAbrir?.addEventListener('click', () => {
                errorDiv.style.display = 'none';
                modal.style.display = 'flex';
            });

            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;

                errorDiv.style.display = 'none';
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

                // Validación inteligente para subida/eliminación de foto de perfil
                if (item.form === 'form-editar-foto') {
                    const fileInput = form.querySelector('#input-foto-file');
                    const eliminarCheckbox = form.querySelector('input[name="eliminar_foto"]');
                    const hasSelectedFile = fileInput && fileInput.files.length > 0;
                    const isEliminarChecked = eliminarCheckbox && eliminarCheckbox.checked;

                    if (!hasSelectedFile && !isEliminarChecked) {
                        errorDiv.textContent = 'Por favor, seleccione una imagen para subir o marque la opción de eliminar la foto actual.';
                        errorDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                        return;
                    }
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    });

                    const result = await response.json();

                    if (result.success) {
                        modal.style.display = 'none';

                        if (typeof CadaModal !== 'undefined') {
                            CadaModal.alert({
                                title: '¡Éxito!',
                                text: result.message || 'Cambios guardados correctamente.',
                                type: 'success',
                                confirmText: 'Aceptar'
                            }).then(() => {
                                const currentTab = new URLSearchParams(window.location.search).get('tab') || 'tab-general';
                                window.location.href = window.location.pathname + '?tab=' + currentTab;
                            });
                        } else {
                            alert(result.message || 'Cambios guardados.');
                            location.reload();
                        }
                    } else {
                        errorDiv.textContent = result.message || 'Error al actualizar.';
                        if (result.errors) {
                            const firstError = Object.values(result.errors)[0][0];
                            errorDiv.textContent += ': ' + firstError;
                        }
                        errorDiv.style.display = 'block';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    errorDiv.textContent = 'Error de conexión.';
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            });
        });

        // Abrir Modal de Asistencia desde el Header
        document.getElementById('btn-header-asistencia')?.addEventListener('click', () => {
            const modal = document.getElementById('modal-historial-asistencia');
            if (modal) modal.style.display = 'flex';
        });

        // Lógica de Direcciones Dinámicas (Estado -> Municipio -> Parroquia)
        const selectPais = document.getElementById('select-pais');
        const selectEstado = document.getElementById('select-estado');
        const selectMunicipio = document.getElementById('select-municipio');
        const selectParroquia = document.getElementById('select-parroquia');

        const baseUrl = "<?= e(url('/api/direcciones')) ?>";

        async function cargarEstados(paisId, selectedId = null) {
            if (!paisId) return;
            try {
                const res = await fetch(`${baseUrl}/estados/${paisId}`);
                const estados = await res.json();
                selectEstado.innerHTML = '<option value="">— Seleccionar —</option>';
                estados.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.estado_id;
                    opt.textContent = e.estado;
                    if (selectedId && e.estado_id == selectedId) opt.selected = true;
                    selectEstado.appendChild(opt);
                });
                if (selectedId) cargarMunicipios(selectedId, <?= (int) ($atleta['municipio_id'] ?? 0) ?>);
            } catch (err) { console.error(err); }
        }

        async function cargarMunicipios(estadoId, selectedId = null) {
            if (!estadoId) return;
            try {
                const res = await fetch(`${baseUrl}/municipios/${estadoId}`);
                const municipios = await res.json();
                selectMunicipio.innerHTML = '<option value="">— Seleccionar —</option>';
                municipios.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.municipio_id;
                    opt.textContent = m.municipio;
                    if (selectedId && m.municipio_id == selectedId) opt.selected = true;
                    selectMunicipio.appendChild(opt);
                });
                if (selectedId) cargarParroquias(selectedId, <?= (int) ($atleta['parroquias_id'] ?? 0) ?>);
            } catch (err) { console.error(err); }
        }

        async function cargarParroquias(municipioId, selectedId = null) {
            if (!municipioId) return;
            try {
                const res = await fetch(`${baseUrl}/parroquias/${municipioId}`);
                const parroquias = await res.json();
                selectParroquia.innerHTML = '<option value="">— Seleccionar —</option>';
                parroquias.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p.parroquia_id;
                    opt.textContent = p.parroquia;
                    if (selectedId && p.parroquia_id == selectedId) opt.selected = true;
                    selectParroquia.appendChild(opt);
                });
            } catch (err) { console.error(err); }
        }

        selectEstado?.addEventListener('change', (e) => cargarMunicipios(e.target.value));
        selectMunicipio?.addEventListener('change', (e) => cargarParroquias(e.target.value));

        // Carga inicial de dirección si existe
        if (selectEstado && <?= (int) ($atleta['estado_id'] ?? 0) ?> > 0) {
            cargarEstados(selectPais.value, <?= (int) ($atleta['estado_id'] ?? 0) ?>);
        } else if (selectEstado) {
            cargarEstados(selectPais.value);
        }

        // 3. Gráfica Real Radar de Pruebas Físicas
        var chartRadar = null;
        const chartRadarDOM = document.getElementById('chart-radar-pruebas');
        const historialPruebasRadar = <?= json_encode($pruebas_historial ?? []) ?>;

        if (chartRadarDOM && typeof echarts !== 'undefined') {
            chartRadar = echarts.init(chartRadarDOM);

            let radarDataSeries = [];
            const colores = [
                { line: 'var(--color-primary)', fill: 'rgba(37, 99, 235, 0.4)' },
                { line: '#10B981', fill: 'rgba(16, 185, 129, 0.3)' } // Verde para la prueba anterior
            ];

            if (historialPruebasRadar.length > 0) {
                // Última prueba (índice 0)
                const p1 = historialPruebasRadar[0];
                let d1 = 'Manual';
                if (p1.fecha_evento) d1 = new Date(p1.fecha_evento).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                radarDataSeries.push({
                    value: [
                        p1.test_de_fuerza || 0,
                        p1.test_velocidad || 0,
                        p1.test_resistencia || 0,
                        p1.test_coordinacion || 0,
                        p1.test_de_reaccion || 0
                    ],
                    name: 'Última: ' + d1,
                    itemStyle: { color: colores[0].line },
                    areaStyle: { color: colores[0].fill },
                    symbol: 'circle',
                    symbolSize: 6
                });

                // Penúltima prueba (índice 1)
                if (historialPruebasRadar.length > 1) {
                    const p2 = historialPruebasRadar[1];
                    let d2 = 'Manual';
                    if (p2.fecha_evento) d2 = new Date(p2.fecha_evento).toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' });
                    radarDataSeries.push({
                        value: [
                            p2.test_de_fuerza || 0,
                            p2.test_velocidad || 0,
                            p2.test_resistencia || 0,
                            p2.test_coordinacion || 0,
                            p2.test_de_reaccion || 0
                        ],
                        name: 'Anterior: ' + d2,
                        itemStyle: { color: colores[1].line },
                        lineStyle: { type: 'dashed' },
                        areaStyle: { color: colores[1].fill },
                        symbol: 'circle',
                        symbolSize: 6
                    });
                }
            } else {
                radarDataSeries.push({
                    value: [0, 0, 0, 0, 0],
                    name: 'Sin Evaluaciones',
                    itemStyle: { color: 'var(--color-text-muted)' },
                    areaStyle: { color: 'rgba(150, 150, 150, 0.1)' }
                });
            }

            const optionRadar = {
                tooltip: { trigger: 'item' },
                legend: { 
                    data: radarDataSeries.map(s => s.name), 
                    bottom: 0,
                    textStyle: { fontSize: 11, color: 'var(--color-text-muted)' }
                },
                radar: {
                    indicator: [
                        { name: 'Fuerza', max: 100 },
                        { name: 'Velocidad', max: 100 },
                        { name: 'Resistencia', max: 100 },
                        { name: 'Coordinación', max: 100 },
                        { name: 'Reacción', max: 100 }
                    ],
                    radius: '60%', // Reducido un poco para dar espacio a la leyenda
                    axisName: { color: 'var(--color-text-muted)', fontWeight: 'bold' },
                    splitArea: {
                        areaStyle: {
                            color: ['rgba(255, 255, 255, 0.05)', 'rgba(200, 200, 200, 0.05)']
                        }
                    }
                },
                series: [{
                    name: 'Rendimiento',
                    type: 'radar',
                    data: radarDataSeries
                }]
            };
            chartRadar.setOption(optionRadar);
        }

        window.addEventListener('resize', () => {
            if (chartAntro) chartAntro.resize();
            if (chartRadar) chartRadar.resize();
        });

        // 1.8.5 Modal de Edición de Pruebas Físicas
        const modalPruebaEditar = document.getElementById('modal-prueba-editar');
        const formPruebaEditar = document.getElementById('form-prueba-editar');

        function cerrarModalPruebaEditar() {
            if (modalPruebaEditar) modalPruebaEditar.style.display = 'none';
        }

        document.querySelectorAll('.btn-editar-prueba').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                const fuerza = btn.getAttribute('data-fuerza');
                const resistencia = btn.getAttribute('data-resistencia');
                const velocidad = btn.getAttribute('data-velocidad');
                const coordinacion = btn.getAttribute('data-coordinacion');
                const reaccion = btn.getAttribute('data-reaccion');

                // Llenar campos
                document.getElementById('edit-prueba-fuerza').value = fuerza || '';
                document.getElementById('edit-prueba-resistencia').value = resistencia || '';
                document.getElementById('edit-prueba-velocidad').value = velocidad || '';
                document.getElementById('edit-prueba-coordinacion').value = coordinacion || '';
                document.getElementById('edit-prueba-reaccion').value = reaccion || '';

                // Ajustar acción de form dinámicamente
                formPruebaEditar.action = `<?= url("/admin/resultados-pruebas") ?>/${id}/editar`;

                document.getElementById('prueba-editar-error').style.display = 'none';
                if (modalPruebaEditar) modalPruebaEditar.style.display = 'flex';
            });
        });

        formPruebaEditar?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const errorDiv = document.getElementById('prueba-editar-error');
            const submitBtn = formPruebaEditar.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;

            errorDiv.style.display = 'none';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Guardando...';

            try {
                const formData = new FormData(formPruebaEditar);
                const response = await fetch(formPruebaEditar.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });

                const result = await response.json();

                if (result.success) {
                    window.location.href = window.location.pathname + '?tab=tab-pruebas';
                } else {
                    errorDiv.textContent = result.message || 'Error al actualizar la prueba.';
                    if (result.errors) {
                        const firstError = Object.values(result.errors)[0][0];
                        errorDiv.textContent += ' ' + firstError;
                    }
                    errorDiv.style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                errorDiv.textContent = 'Error de conexión.';
                errorDiv.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });

        modalPruebaEditar?.querySelectorAll('[data-close-modal]').forEach(btn => {
            btn.addEventListener('click', cerrarModalPruebaEditar);
        });

        // 1.8.6 Eliminación de Pruebas Físicas con CadaModal
        document.querySelectorAll('.btn-eliminar-prueba').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const atletaId = "<?= $atleta['atleta_id'] ?>";

                if (typeof CadaModal !== 'undefined') {
                    CadaModal.confirm({
                        title: '¿Eliminar Prueba Física?',
                        text: '¿Estás seguro de eliminar este registro de pruebas físicas? Esta acción no se puede deshacer.',
                        type: 'danger',
                        confirmText: 'Sí, Eliminar',
                        cancelText: 'Cancelar'
                    }).then((confirmed) => {
                        if (confirmed) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `<?= url('/admin/resultados-pruebas') ?>/${id}/eliminar?atleta_id=${atletaId}&redirect=${encodeURIComponent(window.location.pathname + '?tab=tab-pruebas')}`;

                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_csrf';
                            csrf.value = document.querySelector('meta[name="csrf-token"]').content;

                            form.appendChild(csrf);
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                } else {
                    if (confirm('¿Eliminar esta prueba?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `<?= url('/admin/resultados-pruebas') ?>/${id}/eliminar?atleta_id=${atletaId}&redirect=${encodeURIComponent(window.location.pathname + '?tab=tab-pruebas')}`;

                        const csrf = document.createElement('input');
                        csrf.type = 'hidden';
                        csrf.name = '_csrf';
                        csrf.value = document.querySelector('meta[name="csrf-token"]').content;

                        form.appendChild(csrf);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
            });
        });

        // 5. Asistencia: Calendario Mensual y Gráfico de Dona
        const historialAsistenciasData = <?= json_encode($asistencias_historial ?? []) ?>;
        
        // 5.1 Gráfico de Dona
        var chartDona = null;
        const chartDonaDOM = document.getElementById('chart-asistencia-dona');
        if (chartDonaDOM && typeof echarts !== 'undefined') {
            chartDona = echarts.init(chartDonaDOM);
            
            let countPresente = 0;
            let countAusente = 0;
            let countJustificado = 0;
            let countPartido = 0;
            
            historialAsistenciasData.forEach(a => {
                const tipo = parseInt(a.tipo_actividad);
                const estatus = parseInt(a.estatus);
                
                if (tipo === 0 && estatus === 1) { // Partido y presente
                    countPartido++;
                } else if (estatus === 1) {
                    countPresente++;
                } else if (estatus === 2) {
                    countJustificado++;
                } else if (estatus === 0) {
                    countAusente++;
                }
            });
            
            const total = historialAsistenciasData.length;
            
            const optionDona = {
                tooltip: { trigger: 'item' },
                legend: { bottom: '0%', textStyle: { color: 'var(--color-text-muted)' } },
                series: [
                    {
                        name: 'Asistencia',
                        type: 'pie',
                        radius: ['45%', '70%'],
                        avoidLabelOverlap: false,
                        itemStyle: {
                            borderRadius: 6,
                            borderColor: 'var(--color-bg-alt)',
                            borderWidth: 2
                        },
                        label: { show: false, position: 'center' },
                        emphasis: {
                            label: { show: true, fontSize: 16, fontWeight: 'bold', color: 'var(--color-text)' }
                        },
                        labelLine: { show: false },
                        data: total === 0 ? [{ value: 1, name: 'Sin registros', itemStyle: { color: '#e5e7eb' } }] : [
                            { value: countPresente, name: 'Presente', itemStyle: { color: '#10B981' } },
                            { value: countPartido, name: 'Partido', itemStyle: { color: '#2563EB' } },
                            { value: countJustificado, name: 'Justificado', itemStyle: { color: '#F59E0B' } },
                            { value: countAusente, name: 'Ausente', itemStyle: { color: '#EF4444' } }
                        ].filter(d => d.value > 0)
                    }
                ]
            };
            chartDona.setOption(optionDona);
        }

        // 5.2 Calendario Mensual Interactivo
        let currentYear = new Date().getFullYear();
        let currentMonth = new Date().getMonth(); // 0-11
        
        const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        
        function renderCalendar(year, month) {
            const grid = document.getElementById('calendar-grid');
            const monthLabel = document.getElementById('calendar-month-year');
            if (!grid || !monthLabel) return;
            
            monthLabel.textContent = `${monthNames[month]} ${year}`;
            grid.innerHTML = '';
            
            // Get first day of month (0 = Sun, 1 = Mon)
            let firstDay = new Date(year, month, 1).getDay();
            // Convert to Mon=0, Sun=6
            firstDay = firstDay === 0 ? 6 : firstDay - 1;
            
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            
            const today = new Date();
            const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;
            
            // Generate empty blocks for offset
            for (let i = 0; i < firstDay; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'calendar-day empty';
                grid.appendChild(emptyDiv);
            }
            
            // Generate days
            for (let i = 1; i <= daysInMonth; i++) {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                if (isCurrentMonth && i === today.getDate()) {
                    dayDiv.classList.add('today');
                }
                
                const spanNum = document.createElement('span');
                spanNum.className = 'day-num';
                spanNum.textContent = i;
                dayDiv.appendChild(spanNum);
                
                // Find records for this date
                // Format: YYYY-MM-DD
                const mStr = String(month + 1).padStart(2, '0');
                const dStr = String(i).padStart(2, '0');
                const dateStr = `${year}-${mStr}-${dStr}`;
                
                const dayRecords = historialAsistenciasData.filter(a => {
                    // Check if fecha is defined and starts with dateStr
                    return a.fecha && a.fecha.substring(0, 10) === dateStr;
                });
                
                if (dayRecords.length > 0) {
                    const dotsContainer = document.createElement('div');
                    dotsContainer.className = 'status-dots-container';
                    
                    // Mostrar maximo 3 puntitos
                    dayRecords.slice(0, 3).forEach(r => {
                        const dot = document.createElement('div');
                        dot.className = 'status-dot';
                        const estatus = parseInt(r.estatus);
                        const tipo = parseInt(r.tipo_actividad);
                        
                        if (tipo === 0 && estatus === 1) dot.classList.add('partido');
                        else if (estatus === 1) dot.classList.add('presente');
                        else if (estatus === 2) dot.classList.add('justificado');
                        else if (estatus === 0) dot.classList.add('ausente');
                        
                        const txtEstatus = estatus === 1 ? 'Presente' : (estatus === 2 ? 'Justificado' : 'Ausente');
                        const txtTipo = tipo === 0 ? 'Partido' : (tipo === 1 ? 'Entrenamiento' : 'Otro');
                        dayDiv.title = dayDiv.title ? dayDiv.title + `\n${txtTipo}: ${txtEstatus}` : `${txtTipo}: ${txtEstatus}`;
                        
                        dotsContainer.appendChild(dot);
                    });
                    
                    dayDiv.appendChild(dotsContainer);
                }
                
                grid.appendChild(dayDiv);
            }
        }
        
        renderCalendar(currentYear, currentMonth);
        
        document.getElementById('btn-prev-month')?.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 0) { currentMonth = 11; currentYear--; }
            renderCalendar(currentYear, currentMonth);
        });
        
        document.getElementById('btn-next-month')?.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar(currentYear, currentMonth);
        });

        // Resize hook para la Dona
        window.addEventListener('resize', () => {
            if (chartDona) chartDona.resize();
        });
        // Generic Pagination Script
        function paginateTable(tableId, rowsPerPage = 5) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tbody = table.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Si la tabla dice "No hay registros", ignora
            if (rows.length <= 1 && rows[0] && rows[0].innerText.includes('No hay')) return;

            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / rowsPerPage);
            
            if (totalPages <= 1) return; // No necesita paginación

            // Crear controles
            let controls = document.getElementById(tableId + '-pagination');
            if (!controls) {
                controls = document.createElement('div');
                controls.id = tableId + '-pagination';
                controls.style.display = 'flex';
                controls.style.justifyContent = 'flex-end';
                controls.style.alignItems = 'center';
                controls.style.gap = '8px';
                controls.style.marginTop = '16px';
                table.parentNode.appendChild(controls);
            }

            function render() {
                rows.forEach(r => r.style.display = 'none');
                const start = (currentPage - 1) * rowsPerPage;
                const end = start + rowsPerPage;
                rows.slice(start, end).forEach(r => r.style.display = '');

                controls.innerHTML = '';
                
                const btnPrev = document.createElement('button');
                btnPrev.type = 'button';
                btnPrev.className = 'btn-icon-premium';
                btnPrev.innerHTML = '<i class="ph ph-caret-left"></i>';
                btnPrev.style.width = '32px';
                btnPrev.style.height = '32px';
                btnPrev.disabled = currentPage === 1;
                if(btnPrev.disabled) btnPrev.style.opacity = '0.5';
                btnPrev.onclick = () => { if(currentPage > 1) { currentPage--; render(); } };
                controls.appendChild(btnPrev);

                const span = document.createElement('span');
                span.style.fontSize = '13px';
                span.style.color = 'var(--color-text-muted)';
                span.style.fontWeight = '500';
                span.innerText = `Pág ${currentPage} de ${totalPages}`;
                controls.appendChild(span);

                const btnNext = document.createElement('button');
                btnNext.type = 'button';
                btnNext.className = 'btn-icon-premium';
                btnNext.innerHTML = '<i class="ph ph-caret-right"></i>';
                btnNext.style.width = '32px';
                btnNext.style.height = '32px';
                btnNext.disabled = currentPage === totalPages;
                if(btnNext.disabled) btnNext.style.opacity = '0.5';
                btnNext.onclick = () => { if(currentPage < totalPages) { currentPage++; render(); } };
                controls.appendChild(btnNext);
            }
            render();
        }

        // ── Widget de Cédula/Folio para Modales de Edición ───────────────────────────
        function formatCedulaNumber(val) {
            let digits = val.replace(/\D/g, '').substring(0, 8);
            return digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function setupCedulaWidget(prefixId, numberId, hiddenId) {
            const prefixEl = document.getElementById(prefixId);
            const numberEl = document.getElementById(numberId);
            const hiddenEl = document.getElementById(hiddenId);
            if (!prefixEl || !numberEl || !hiddenEl) return;

            function sync() {
                let val = numberEl.value.trim().toUpperCase();
                if (prefixEl.value === 'V' || prefixEl.value === 'E') {
                    val = formatCedulaNumber(val);
                } else {
                    val = val.replace(/[^A-Z0-9]/g, '').substring(0, 10);
                }
                numberEl.value = val;
                hiddenEl.value = val.length ? prefixEl.value + '-' + val : '';
            }

            if (hiddenEl.value) {
                let raw = hiddenEl.value;
                let prefix = 'V', num = raw;
                if (raw.includes('-')) {
                    let parts = raw.split('-');
                    prefix = parts[0];
                    num = parts[1];
                } else {
                    let firstChar = raw.charAt(0).toUpperCase();
                    if (['V', 'E', 'F'].includes(firstChar)) {
                        prefix = firstChar;
                        num = raw.substring(1);
                    }
                }
                prefixEl.value = prefix;
                numberEl.value = prefix === 'V' || prefix === 'E' ? formatCedulaNumber(num) : num;
            }
            sync();

            numberEl.addEventListener('input', sync);
            prefixEl.addEventListener('change', () => {
                if (prefixEl.value === 'F') {
                    numberEl.placeholder = "Nro de Folio";
                    numberEl.maxLength = 10;
                } else {
                    numberEl.placeholder = "12.345.678";
                    numberEl.maxLength = 10;
                }
                sync();
                numberEl.focus();
            });
        }

        setupCedulaWidget('cedula_prefix', 'cedula_number', 'cedula');
        setupCedulaWidget('tutor_cedula_prefix', 'tutor_cedula_number', 'tutor_cedula');

        paginateTable('tabla-asistencias', 5);
        paginateTable('tabla-antropometria', 5);
        paginateTable('tabla-pruebas', 5);

    });
</script>