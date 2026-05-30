<?php /** @var array $stats @var array $dataCategorias @var array $dataAsistencia @var array $dataDemografia @var array $dataActividades @var array $dataEntrenadores */ $user = auth() ?? []; ?>

<div class="welcome-card">
    <div class="wc-avatar"><?= strtoupper(mb_substr($user['nombre'] ?? '?', 0, 1)) ?></div>
    <div>
        <div class="wc-title">Bienvenido, <?= e(($user['nombre'] ?? '') . ' ' . ($user['apellido'] ?? '')) ?></div>
        <div class="wc-sub"><?= e($user['nombre_rol'] ?? 'Administrador') ?> — Club Atlético Deportivo Acarigua</div>
    </div>
</div>

<h3 style="font-family: var(--font-display); margin-bottom: 16px;">Accesos Rápidos</h3>
<div class="quick-grid">
    <a href="<?= e(url('/admin/atletas')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-users"></i></div>
        <div>
            <div class="qc-title">Atletas</div>
            <div class="qc-desc">Gestión del equipo</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/asistencias/crear')) ?>" class="quick-card">
        <div class="qc-icon blue"><i class="ph ph-clipboard-text"></i></div>
        <div>
            <div class="qc-title">Asistencia</div>
            <div class="qc-desc">Registrar asistencia</div>
        </div>
    </a>

    <a href="<?= e(url('/admin/categorias')) ?>" class="quick-card">
        <div class="qc-icon red"><i class="ph ph-folders"></i></div>
        <div>
            <div class="qc-title">Categorías</div>
            <div class="qc-desc"><?= (int) $stats['categorias'] ?> activas</div>
        </div>
    </a>
</div>

<h3 style="font-family: var(--font-display); margin-bottom: 16px;">Contadores Generales</h3>
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-primary);"><?= (int) $stats['atletas'] ?></div>
        <div class="stat-label">Atletas registrados</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-success);"><?= (int) $stats['activos'] ?></div>
        <div class="stat-label">Atletas activos</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-info);"><?= (int) $stats['categorias'] ?></div>
        <div class="stat-label">Categorías activas</div>
    </div>
    <div class="stat-card">
        <div class="stat-number" style="color: var(--color-warning);"><?= (int) ($stats['usuarios'] ?? 0) ?></div>
        <div class="stat-label">Usuarios del sistema</div>
    </div>
</div>


<h3 style="font-family: var(--font-display); margin-top: 32px; margin-bottom: 16px;">Análisis General</h3>
<div class="dashboard-charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; margin-bottom: 32px;">
    <!-- Gráfica 1: Distribución por Categoría -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-tag" style="color: var(--color-primary);"></i> Distribución por Categoría
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-categorias"></canvas>
        </div>
    </div>

    <!-- Gráfica 3: Demografía por Rango de Edad y Género -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-gender-intersex" style="color: var(--color-info);"></i> Pirámide Demográfica
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-demografia"></canvas>
        </div>
    </div>

    <!-- Gráfica 5: Carga de Atletas por Entrenador -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-users-three" style="color: var(--color-primary);"></i> Carga por Entrenador
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-entrenadores"></canvas>
        </div>
    </div>

    <!-- Gráfica 4: Historial de Actividades por Tipo al Mes -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-calendar" style="color: var(--color-warning);"></i> Actividades Mensuales
        </h4>
        <div style="flex: 1; position: relative; width: 100%;">
            <canvas id="chart-actividades"></canvas>
        </div>
    </div>

    <!-- Gráfica 2: Tasa de Asistencia Mensual por Categoría -->
    <div class="card" style="padding: 20px; display: flex; flex-direction: column; min-height: 380px; grid-column: 1 / -1;">
        <h4 style="margin: 0 0 16px 0; font-family: var(--font-display); font-size: 15px; display: flex; align-items: center; gap: 8px;">
            <i class="ph ph-chart-bar" style="color: var(--color-success);"></i> Tasa de Asistencia por Categoría
        </h4>
        <div style="flex: 1; position: relative; width: 100%; min-height: 250px;">
            <canvas id="chart-asistencia"></canvas>
        </div>
    </div>
</div>


<!-- Incluir CDN de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. Obtener y configurar tema/paleta de colores dinámicos
    const getColors = () => {
        const style = getComputedStyle(document.body);
        return {
            text: style.getPropertyValue('--color-text').trim() || '#e5e7eb',
            textMuted: style.getPropertyValue('--color-text-muted').trim() || '#9ca3af',
            border: style.getPropertyValue('--color-border').trim() || '#374151',
            primary: style.getPropertyValue('--color-primary').trim() || '#DE0A26',
            success: style.getPropertyValue('--color-success').trim() || '#10B981',
            info: style.getPropertyValue('--color-info').trim() || '#3B82F6',
            warning: style.getPropertyValue('--color-warning').trim() || '#F59E0B'
        };
    };

    let colors = getColors();

    // Datos inyectados desde el servidor
    const dataCategoriasRaw = <?= json_encode($dataCategorias) ?>;
    const dataAsistenciaRaw = <?= json_encode($dataAsistencia) ?>;
    const dataDemografiaRaw = <?= json_encode($dataDemografia) ?>;
    const dataActividadesRaw = <?= json_encode($dataActividades) ?>;
    const dataEntrenadoresRaw = <?= json_encode($dataEntrenadores) ?>;

    const chartInstances = [];

    // --- CONFIGURACIÓN DE GRÁFICOS ---

    // 1. Gráfica de Dona: Distribución por Categoría
    const ctxCategorias = document.getElementById('chart-categorias').getContext('2d');
    const chartCategorias = new Chart(ctxCategorias, {
        type: 'doughnut',
        data: {
            labels: dataCategoriasRaw.map(x => x.nombre_categoria),
            datasets: [{
                data: dataCategoriasRaw.map(x => x.total),
                backgroundColor: [
                    '#DE0A26', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'
                ],
                borderColor: 'transparent'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: colors.text }
                }
            }
        }
    });
    chartInstances.push(chartCategorias);

    // 2. Gráfica de Barras Apiladas: Pirámide Demográfica
    const rangosEdades = ['Sub-10', 'Sub-13', 'Sub-16', 'Sub-20/Mayores'];
    const dataM = rangosEdades.map(r => {
        const found = dataDemografiaRaw.find(x => x.sexo === 'M' && x.rango_edad === r);
        return found ? parseInt(found.total) : 0;
    });
    const dataF = rangosEdades.map(r => {
        const found = dataDemografiaRaw.find(x => x.sexo === 'F' && x.rango_edad === r);
        return found ? parseInt(found.total) : 0;
    });

    const ctxDemografia = document.getElementById('chart-demografia').getContext('2d');
    const chartDemografia = new Chart(ctxDemografia, {
        type: 'bar',
        data: {
            labels: rangosEdades,
            datasets: [
                { label: 'Masculino', data: dataM, backgroundColor: '#3B82F6', borderRadius: 4 },
                { label: 'Femenino', data: dataF, backgroundColor: '#EC4899', borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: true, grid: { color: colors.border }, ticks: { color: colors.text } },
                y: { stacked: true, grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } }
            },
            plugins: {
                legend: { labels: { color: colors.text } }
            }
        }
    });
    chartInstances.push(chartDemografia);

    // 3. Gráfica de Barras Horizontal: Carga por Entrenador
    const ctxEntrenadores = document.getElementById('chart-entrenadores').getContext('2d');
    const chartEntrenadores = new Chart(ctxEntrenadores, {
        type: 'bar',
        data: {
            labels: dataEntrenadoresRaw.map(x => x.entrenador),
            datasets: [{
                label: 'Atletas',
                data: dataEntrenadoresRaw.map(x => x.total_atletas),
                backgroundColor: colors.primary,
                borderRadius: 4
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } },
                y: { grid: { color: colors.border }, ticks: { color: colors.text } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
    chartInstances.push(chartEntrenadores);

    // 4. Gráfica de Línea: Actividades Mensuales
    const mesesActividades = [...new Set(dataActividadesRaw.map(x => x.mes))].sort();
    const tiposActividades = [
        { id: 0, label: 'Partido', color: '#10B981' },
        { id: 1, label: 'Entrenamiento', color: '#3B82F6' },
        { id: 2, label: 'Pruebas Físicas', color: '#F59E0B' },
        { id: 3, label: 'Evento Especial', color: '#8B5CF6' }
    ];

    const datasetsActividades = tiposActividades.map(t => ({
        label: t.label,
        data: mesesActividades.map(m => {
            const found = dataActividadesRaw.find(x => x.mes === m && parseInt(x.tipo_actividad) === t.id);
            return found ? parseInt(found.total) : 0;
        }),
        borderColor: t.color,
        backgroundColor: t.color + '22',
        fill: true,
        tension: 0.3
    }));

    const ctxActividades = document.getElementById('chart-actividades').getContext('2d');
    const chartActividades = new Chart(ctxActividades, {
        type: 'line',
        data: {
            labels: mesesActividades,
            datasets: datasetsActividades
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: colors.border }, ticks: { color: colors.text } },
                y: { grid: { color: colors.border }, ticks: { color: colors.text, precision: 0 } }
            },
            plugins: {
                legend: { labels: { color: colors.text } }
            }
        }
    });
    chartInstances.push(chartActividades);

    // 5. Gráfica de Barras Agrupadas: Asistencia Mensual por Categoría
    const mesesAsistencia = [...new Set(dataAsistenciaRaw.map(x => x.mes))].sort();
    const categoriasAsistencia = [...new Set(dataAsistenciaRaw.map(x => x.nombre_categoria))];
    const colorsAsistencia = ['#DE0A26', '#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#6B7280'];

    const datasetsAsistencia = categoriasAsistencia.map((cat, idx) => ({
        label: cat,
        data: mesesAsistencia.map(m => {
            const found = dataAsistenciaRaw.find(x => x.nombre_categoria === cat && x.mes === m);
            if (found && parseInt(found.total_registros) > 0) {
                return Math.round((parseInt(found.presentes) * 100) / parseInt(found.total_registros));
            }
            return 0;
        }),
        backgroundColor: colorsAsistencia[idx % colorsAsistencia.length],
        borderRadius: 4
    }));

    const ctxAsistencia = document.getElementById('chart-asistencia').getContext('2d');
    const chartAsistencia = new Chart(ctxAsistencia, {
        type: 'bar',
        data: {
            labels: mesesAsistencia,
            datasets: datasetsAsistencia
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { color: colors.border }, ticks: { color: colors.text } },
                y: { 
                    grid: { color: colors.border }, 
                    ticks: { 
                        color: colors.text,
                        callback: value => value + "%"
                    },
                    max: 100
                }
            },
            plugins: {
                legend: { labels: { color: colors.text } },
                tooltip: {
                    callbacks: {
                        label: context => `${context.dataset.label}: ${context.raw}%`
                    }
                }
            }
        }
    });
    chartInstances.push(chartAsistencia);

    // --- MANEJO DE CAMBIO DE TEMA DINÁMICO ---
    const updateChartThemes = () => {
        colors = getColors();
        chartInstances.forEach(chart => {
            // Actualizar color de la leyenda
            if (chart.options.plugins && chart.options.plugins.legend && chart.options.plugins.legend.labels) {
                chart.options.plugins.legend.labels.color = colors.text;
            }
            // Actualizar color de ejes
            if (chart.options.scales) {
                Object.keys(chart.options.scales).forEach(scaleKey => {
                    const scale = chart.options.scales[scaleKey];
                    if (scale.grid) scale.grid.color = colors.border;
                    if (scale.ticks) scale.ticks.color = colors.text;
                });
            }
            // Actualizar datasets individuales si usan colores de marca del tema
            if (chart === chartEntrenadores) {
                chart.data.datasets[0].backgroundColor = colors.primary;
            }
            chart.update();
        });
    };

    // Escuchar el evento click en el botón de cambiar tema
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            setTimeout(updateChartThemes, 100);
        });
    });
});
</script>