<?php $active = $active ?? ''; ?>
<aside class="sidebar">
    <a href="<?= e(url('/admin')) ?>" class="sidebar__brand" style="text-decoration:none;">
        <div class="brand__logo">CADA</div>
        <div class="brand__text">
            <div class="title">Club Atlético</div>
            <div class="subtitle">Deportivo Acarigua</div>
        </div>
    </a>

    <ul class="sidebar__nav">
        <!-- Inicio (enlace directo, no desplegable) -->
        <li>
            <a href="<?= e(url('/admin')) ?>" class="<?= $active === 'inicio' ? 'active' : '' ?>">
                <span class="icon"><i class="ph ph-house"></i></span>
                <span class="nav-text">Inicio</span>
            </a>
        </li>

        <!-- Gestión Deportiva -->
        <li class="sidebar__has-sub <?= in_array($active, ['categorias', 'atletas']) ? 'is-open' : '' ?>">
            <a href="#">
                <span class="icon"><i class="ph ph-soccer-ball"></i></span>
                <span class="nav-text">Gestión Deportiva</span>
            </a>
            <ul class="sidebar__submenu <?= in_array($active, ['categorias', 'atletas']) ? 'is-open' : '' ?>">
                <li>
                    <a href="<?= e(url('/admin/categorias')) ?>" class="<?= $active === 'categorias' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-folders"></i></span>
                        <span class="nav-text">Categorías</span>
                    </a>
                </li>
                <li>
                    <a href="<?= e(url('/admin/atletas')) ?>" class="<?= $active === 'atletas' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-users"></i></span>
                        <span class="nav-text">Atletas</span>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Control y Seguimiento -->
        <li class="sidebar__has-sub <?= in_array($active, ['asistencias']) ? 'is-open' : '' ?>">
            <a href="#">
                <span class="icon"><i class="ph ph-clipboard-text"></i></span>
                <span class="nav-text">Control y Seguimiento</span>
            </a>
            <ul class="sidebar__submenu <?= in_array($active, ['asistencias']) ? 'is-open' : '' ?>">
                <li>
                    <a href="<?= e(url('/admin/asistencias')) ?>" class="<?= $active === 'asistencias' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-calendar-check"></i></span>
                        <span class="nav-text">Asistencias</span>
                    </a>
                </li>
                <?php /*
                ============================================================
                ANTROPOMETRÍA Y PRUEBAS FÍSICAS — OCULTOS TEMPORALMENTE
                Estos módulos son redundantes porque el registro y consulta
                de medidas y pruebas ya se realiza desde el perfil del atleta
                (tabs Antropometría y Pruebas Físicas).
                Descomentar si se implementa registro masivo o comparativas.
                ============================================================
                <li>
                    <a href="<?= e(url('/admin/medidas')) ?>" class="<?= $active === 'medidas' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-ruler"></i></span>
                        <span class="nav-text">Antropometría</span>
                    </a>
                </li>
                <li>
                    <a href="<?= e(url('/admin/resultados-pruebas')) ?>" class="<?= $active === 'resultados_pruebas' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-timer"></i></span>
                        <span class="nav-text">Pruebas Físicas</span>
                    </a>
                </li>
                */ ?>
            </ul>
        </li>

        <?php /*
        ============================================================
        ANÁLISIS DEPORTIVO — OCULTO TEMPORALMENTE
        Módulo planificado para comparativas antropométricas,
        de rendimiento e historial de partidos.
        No implementado en este trayecto por limitación de tiempo.
        Nota: historial_partidos no está vinculado a actividades,
        se requiere definir la relación antes de implementar.
        ============================================================
        <!-- Análisis Deportivo -->
        <li class="sidebar__has-sub sidebar__disabled">
            <a href="#" class="sidebar__link-disabled" title="Próximamente">
                <span class="icon"><i class="ph ph-chart-line-up"></i></span>
                <span class="nav-text">Análisis Deportivo</span>
            </a>
            <ul class="sidebar__submenu">
                <li>
                    <a href="#" class="sidebar__link-disabled" tabindex="-1">
                        <span class="icon"><i class="ph ph-chart-bar"></i></span>
                        <span class="nav-text">Comparativa Antropométrica</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar__link-disabled" tabindex="-1">
                        <span class="icon"><i class="ph ph-trophy"></i></span>
                        <span class="nav-text">Comparativa de Rendimiento</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sidebar__link-disabled" tabindex="-1">
                        <span class="icon"><i class="ph ph-flag-banner"></i></span>
                        <span class="nav-text">Historial de Partidos</span>
                    </a>
                </li>
            </ul>
        </li>
        */ ?>

        <?php /*
        ============================================================
        MÓDULO DE REPORTES — OCULTO TEMPORALMENTE
        Los reportes individuales se generan desde el perfil del atleta
        y los de categoría desde la vista de categorías.
        Descomentar este bloque si se requiere un centro de reportes
        centralizado con reportes adicionales (asistencia, evolución, etc.)
        ============================================================
        <!-- Reportes -->
        <li class="sidebar__has-sub <?= $active === 'reportes' ? 'is-open' : '' ?>">
            <a href="#">
                <span class="icon"><i class="ph ph-printer"></i></span>
                <span class="nav-text">Reportes</span>
            </a>
            <ul class="sidebar__submenu <?= $active === 'reportes' ? 'is-open' : '' ?>">
                <li>
                    <a href="<?= e(url('/admin/reportes')) ?>" class="<?= $active === 'reportes' ? 'active' : '' ?>">
                        <span class="icon"><i class="ph ph-file-text"></i></span>
                        <span class="nav-text">Ficha Individual</span>
                    </a>
                </li>
            </ul>
        </li>
        */ ?>

        <?php if (\App\Core\Auth::isAdmin()): ?>
            <!-- Administración -->
            <li class="sidebar__has-sub <?= in_array($active, ['usuarios', 'configuracion']) ? 'is-open' : '' ?>">
                <a href="#">
                    <span class="icon"><i class="ph ph-shield-check"></i></span>
                    <span class="nav-text">Administración</span>
                </a>
                <ul class="sidebar__submenu <?= in_array($active, ['usuarios', 'configuracion']) ? 'is-open' : '' ?>">
                    <li>
                        <a href="<?= e(url('/admin/usuarios')) ?>" class="<?= $active === 'usuarios' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-user-gear"></i></span>
                            <span class="nav-text">Gestión de Usuarios</span>
                        </a>
                    </li>
                    <li>
                        <a href="<?= e(url('/admin/configuracion')) ?>" class="<?= $active === 'configuracion' ? 'active' : '' ?>">
                            <span class="icon"><i class="ph ph-gear"></i></span>
                            <span class="nav-text">Configuración</span>
                        </a>
                    </li>
                </ul>
            </li>
        <?php endif; ?>
    </ul>
</aside>