<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Atleta;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;
use App\Core\Database;
use DateTime;

final class ReporteService
{
    /**
     * Arma la ficha técnica individual de un atleta y la entrega como PDF/HTML.
     *
     * @return array{mime:string, filename:string, content:string}|null
     */
    public function fichaAtleta(int $atletaId): ?array
    {
        $atleta = (new Atleta())->findCompleto($atletaId);
        if (!$atleta) return null;

        $antropometria = (new MedidaAntropometrica())->historial($atletaId);
        $pruebas       = (new ResultadoPrueba())->historial($atletaId);
        $asistencia    = (new Asistencia())->resumenAtleta($atletaId);

        $html = $this->construirHtml($atleta, $antropometria, $pruebas, $asistencia);

        $filename = 'ficha_' . preg_replace('/[^a-z0-9]+/i', '_', $atleta['nombre'] . '_' . $atleta['apellido']) . '_' . date('Ymd');
        
        if (class_exists('App\Services\PdfGenerator')) {
            return (new PdfGenerator())->render(
                'Ficha Técnica - ' . $atleta['nombre'] . ' ' . $atleta['apellido'],
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    /**
     * Genera un listado general de todos los atletas registrados.
     *
     * @return array{mime:string, filename:string, content:string}
     */
    public function listaAtletas(): array
    {
        $db = Database::connection();
        $sql = "
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.estatus,
                   c.nombre_categoria
            FROM atletas a
            LEFT JOIN categorias c ON c.categoria_id = a.categoria_id
            ORDER BY a.apellido, a.nombre
        ";
        $atletas = $db->query($sql)->fetchAll();

        $html = $this->construirHtmlLista($atletas);

        $filename = 'listado_atletas_' . date('Ymd');
        
        if (class_exists('App\Services\PdfGenerator')) {
            return (new PdfGenerator())->render(
                'Listado General de Atletas - CADA',
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    /**
     * Genera un reporte detallado de los atletas de una categoría específica.
     *
     * @param int $categoriaId
     * @return array{mime:string, filename:string, content:string}|null
     */
    public function reportePorCategoria(int $categoriaId): ?array
    {
        $db = Database::connection();
        
        $stmt = $db->prepare("
            SELECT c.*, u.nombre as entrenador 
            FROM categorias c 
            LEFT JOIN usuarios u ON u.usuario_id = c.usuario_id 
            WHERE c.categoria_id = :id
        ");
        $stmt->execute([':id' => $categoriaId]);
        $categoria = $stmt->fetch();
        
        if (!$categoria) return null;

        $stmt = $db->prepare("
            SELECT a.atleta_id, a.nombre, a.apellido, a.cedula, a.telefono, a.estatus, a.fecha_nac
            FROM atletas a
            WHERE a.categoria_id = :id
            ORDER BY a.apellido, a.nombre
        ");
        $stmt->execute([':id' => $categoriaId]);
        $atletas = $stmt->fetchAll();

        $html = $this->construirHtmlCategoria($categoria, $atletas);

        $filename = 'reporte_categoria_' . preg_replace('/[^a-z0-9]+/i', '_', $categoria['nombre_categoria']) . '_' . date('Ymd');
        
        if (class_exists('App\Services\PdfGenerator')) {
            return (new PdfGenerator())->render(
                'Reporte Categoría: ' . $categoria['nombre_categoria'],
                $html,
                strtolower($filename)
            );
        }
        
        return [
            'mime' => 'text/html',
            'filename' => $filename . '.html',
            'content' => $html
        ];
    }

    private function construirHtml(array $a, array $antropo, array $pruebas, array $asistencia): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $nombreCompleto = $esc($a['nombre'] . ' ' . $a['apellido']);
        $fechaNac = $a['fecha_nac'] ?? null;
        $edad = $fechaNac ? (new DateTime($fechaNac))->diff(new DateTime('today'))->y : null;

        $antropoRows = '';
        foreach ($antropo as $m) {
            $antropoRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($m['fecha_medicion']), $esc($m['peso']), $esc($m['altura']),
                $esc($m['envergadura']), $esc($m['porcentaje_grasa']), $esc($m['porcentaje_musculatura'])
            );
        }
        $antropoTable = $antropoRows ? "<table class=\"data-table\"><thead><tr><th>Fecha</th><th>Peso (kg)</th><th>Altura (cm)</th><th>Envergadura</th><th>% Grasa</th><th>% Musc.</th></tr></thead><tbody>$antropoRows</tbody></table>" : '<p>Sin mediciones antropométricas registradas.</p>';

        $pruebasRows = '';
        foreach ($pruebas as $p) {
            $pruebasRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($p['fecha_evento']), $esc($p['test_de_fuerza']), $esc($p['test_resistencia']),
                $esc($p['test_velocidad']), $esc($p['test_coordinacion']), $esc($p['test_de_reaccion'])
            );
        }
        $pruebasTable = $pruebasRows ? "<table class=\"data-table\"><thead><tr><th>Fecha</th><th>Fuerza</th><th>Resistencia</th><th>Velocidad</th><th>Coordinación</th><th>Reacción</th></tr></thead><tbody>$pruebasRows</tbody></table>" : '<p>Sin pruebas físicas registradas.</p>';

        $asiMap = [1 => 0, 0 => 0, 2 => 0];
        foreach ($asistencia as $row) { $asiMap[(int)$row['estatus']] = (int) $row['total']; }
        $totalAsi = array_sum($asiMap) ?: 1;
        $pctPresente = round(($asiMap[1] / $totalAsi) * 100, 1);

        $direccionParts = array_filter([$a['localidad'] ?? null, $a['parroquia_nombre'] ?? null, $a['municipio_nombre'] ?? null, $a['estado_nombre'] ?? null]);
        $direccion = $direccionParts ? $esc(implode(', ', $direccionParts)) : '—';
        $estatusTexto = ((int)$a['estatus'] === 1) ? 'Activo' : (((int)$a['estatus'] === 2) ? 'Lesionado' : 'Suspendido');
        $fechaGeneracion = $esc(date('d/m/Y h:i A'));

        return <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.4; }
    .document-header { text-align: center; padding-bottom: 20px; margin-bottom: 30px; border-bottom: 2px solid #800020; }
    .club-brand { font-size: 32px; font-weight: bold; color: #800020; letter-spacing: 4px; margin-bottom: 5px; }
    .club-full-name { font-size: 14px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 10px; }
    .report-title { background-color: #800020; color: #ffffff; padding: 8px 15px; font-size: 18px; display: inline-block; margin-top: 10px; text-transform: uppercase; font-weight: bold; }
    .report-meta { color: #888; font-size: 10px; margin-top: 10px; }
    .section { margin-bottom: 25px; }
    .section-header { background-color: #f8f9fa; border-left: 5px solid #800020; padding: 8px 12px; margin-bottom: 15px; font-size: 15px; font-weight: bold; color: #800020; text-transform: uppercase; }
    table.info-grid { width: 100%; margin-bottom: 10px; }
    table.info-grid td { padding: 6px 4px; vertical-align: top; border-bottom: 1px solid #f0f0f0; }
    .info-label { font-weight: bold; color: #800020; width: 40%; font-size: 11px; }
    .info-value { color: #333; width: 60%; font-size: 11px; }
    table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.data-table th { background-color: #800020; color: #ffffff; font-size: 10px; font-weight: bold; padding: 10px 5px; text-align: center; border: 1px solid #800020; }
    table.data-table td { font-size: 10px; padding: 8px 5px; text-align: center; border: 1px solid #e9ecef; }
    table.data-table tr:nth-child(even) { background-color: #f8f9fa; }
    .status-badge { color: #800020; border: 1px solid #800020; padding: 2px 10px; font-size: 12px; font-weight: bold; }
</style>
<div class="document-header">
    <div class="club-brand">CADA</div>
    <div class="club-full-name">Club Atlético Deportivo Acarigua</div>
    <div class="report-title">Ficha Técnica Individual</div>
    <div class="report-meta">Generado el {$fechaGeneracion}</div>
</div>
<div class="section">
    <table width="100%">
        <tr>
            <td width="70%">
                <h2 style="margin:0; color:#1a1a1a; font-size: 22px;">{$nombreCompleto}</h2>
                <p style="margin:5px 0 15px 0;"><span class="status-badge">{$esc($estatusTexto)}</span></p>
            </td>
        </tr>
    </table>
    <table class="info-grid">
        <tr>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Cédula:</td><td class="info-value">{$esc($a['cedula'])}</td></tr>
                    <tr><td class="info-label">Nacimiento:</td><td class="info-value">{$esc($fechaNac)} ({$esc($edad)} años)</td></tr>
                    <tr><td class="info-label">Teléfono:</td><td class="info-value">{$esc($a['telefono'])}</td></tr>
                    <tr><td class="info-label">Categoría:</td><td class="info-value">{$esc($a['nombre_categoria'])}</td></tr>
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Representante:</td><td class="info-value">{$esc(($a['tutor_nombres'] ?? '') . ' ' . ($a['tutor_apellidos'] ?? ''))}</td></tr>
                    <tr><td class="info-label">C.I. Rep:</td><td class="info-value">{$esc($a['tutor_cedula'])}</td></tr>
                    <tr><td class="info-label">Tlf. Rep:</td><td class="info-value">{$esc($a['tutor_telefono'])}</td></tr>
                    <tr><td class="info-label">Dirección:</td><td class="info-value">{$direccion}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<div class="section"><div class="section-header">Historial Antropométrico</div>{$antropoTable}</div>
<div class="section"><div class="section-header">Pruebas Físicas</div>{$pruebasTable}</div>
HTML;
    }

    private function construirHtmlCategoria(array $cat, array $atletas): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $fechaGeneracion = $esc(date('d/m/Y h:i A'));
        $rows = '';
        foreach ($atletas as $index => $a) {
            $estatusTexto = ((int)$a['estatus'] === 1) ? 'Activo' : (((int)$a['estatus'] === 2) ? 'Lesionado' : 'Suspendido');
            $edad = $a['fecha_nac'] ? (new DateTime($a['fecha_nac']))->diff(new DateTime('today'))->y : '—';
            $rows .= sprintf(
                '<tr>
                    <td width="5%%" style="color:#999;">%d</td>
                    <td width="35%%" style="text-align:left; font-weight:bold; color:#1a1a1a;">%s</td>
                    <td width="15%%">%s</td>
                    <td width="12%%">%s años</td>
                    <td width="18%%">%s</td>
                    <td width="15%%"><span style="font-weight:bold; color:#800020;">%s</span></td>
                </tr>',
                $index + 1, $esc($a['apellido'] . ', ' . $a['nombre']), $esc($a['cedula']), $esc($edad), $esc($a['telefono']), $esc($estatusTexto)
            );
        }

        return <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.4; }
    .document-header { text-align: center; padding-bottom: 20px; margin-bottom: 25px; border-bottom: 2px solid #800020; }
    .club-brand { font-size: 30px; font-weight: bold; color: #800020; letter-spacing: 4px; margin-bottom: 4px; }
    .club-full-name { font-size: 11px; font-weight: bold; color: #555; text-transform: uppercase; margin-bottom: 12px; }
    .report-title { background-color: #800020; color: #ffffff; padding: 8px 25px; font-size: 16px; display: inline-block; font-weight: bold; text-transform: uppercase; }
    .category-summary { margin: 25px 0; padding: 0; background-color: #ffffff; border: 1px solid #e0e0e0; border-top: 5px solid #800020; }
    .summary-header { background-color: #fcfcfc; padding: 15px; text-align: center; border-bottom: 1px solid #f0f0f0; }
    .summary-title { font-size: 24px; font-weight: bold; color: #1a1a1a; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
    .summary-body { padding: 20px; }
    .info-table { width: 100%; border: none; }
    .info-label { font-weight: bold; color: #800020; width: 35%; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    .info-value { color: #333; width: 65%; font-size: 12px; font-weight: bold; }
    .section-label { font-size: 14px; font-weight: bold; color: #800020; text-transform: uppercase; margin-top: 35px; margin-bottom: 15px; border-bottom: 2px solid #800020; display: inline-block; padding-bottom: 3px; }
    table.member-list { width: 100%; border-collapse: collapse; margin-top: 5px; }
    table.member-list th { background-color: #f8f9fa; color: #800020; font-size: 8px; font-weight: bold; padding: 8px 4px; text-align: center; border: 1px solid #dee2e6; text-transform: uppercase; }
    table.member-list td { font-size: 8px; padding: 8px 4px; text-align: center; border: 1px solid #f1f1f1; vertical-align: middle; }
    table.member-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="club-brand">CADA</div>
    <div class="club-full-name">Club Atlético Deportivo Acarigua</div>
    <div class="report-title">Reporte de Categoría Deportiva</div>
    <div class="report-meta">Documento oficial · Generado el {$fechaGeneracion}</div>
</div>
<div class="category-summary">
    <div class="summary-header"><div class="summary-title">{$esc($cat['nombre_categoria'])}</div></div>
    <div class="summary-body">
        <table class="info-table" cellpadding="2">
            <tr>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Entrenador:</td><td class="info-value" width="60%">{$esc($cat['entrenador'])}</td></tr>
                        <tr><td class="info-label" width="40%">Rango Edad:</td><td class="info-value" width="60%">{$esc($cat['edad_min'])} a {$esc($cat['edad_max'])} años</td></tr>
                    </table>
                </td>
                <td width="50%">
                    <table width="100%">
                        <tr><td class="info-label" width="40%">Género:</td><td class="info-value" width="60%">{$esc($cat['sexo_categoria'] === 'F' ? 'Femenino' : ($cat['sexo_categoria'] === 'M' ? 'Masculino' : 'Mixto'))}</td></tr>
                        <tr><td class="info-label" width="40%">Integrantes:</td><td class="info-value" width="60%">{$esc(count($atletas))} atletas registrados</td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</div>
<div class="section-label">Listado de Integrantes</div>
<table class="member-list" cellpadding="4">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="35%" style="text-align:left;">Atleta (Apellido, Nombre)</th>
            <th width="15%">Cédula</th>
            <th width="12%">Edad</th>
            <th width="18%">Teléfono</th>
            <th width="15%">Estatus</th>
        </tr>
    </thead>
    <tbody>
        {$rows}
    </tbody>
</table>
HTML;
    }

    private function construirHtmlLista(array $atletas): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $fechaGeneracion = $esc(date('d/m/Y h:i A'));
        $rows = '';
        foreach ($atletas as $index => $a) {
            $estatusTexto = ((int)$a['estatus'] === 1) ? 'Activo' : (((int)$a['estatus'] === 2) ? 'Lesionado' : 'Suspendido');
            $rows .= sprintf(
                '<tr><td style="width:25px;color:#999;">%d</td><td style="text-align:left;font-weight:bold;color:#1a1a1a;">%s</td><td>%s</td><td>%s</td><td>%s</td><td><span style="font-weight:bold;color:#800020;">%s</span></td></tr>',
                $index + 1, $esc($a['apellido'] . ', ' . $a['nombre']), $esc($a['cedula']), $esc($a['nombre_categoria']), $esc($a['telefono']), $esc($estatusTexto)
            );
        }

        return <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; }
    .document-header { text-align: center; padding-bottom: 15px; margin-bottom: 25px; border-bottom: 2px solid #800020; }
    .club-brand { font-size: 28px; font-weight: bold; color: #800020; letter-spacing: 3px; }
    .report-title { background-color: #800020; color: #ffffff; padding: 6px 20px; font-size: 14px; display: inline-block; font-weight: bold; text-transform: uppercase; }
    table.main-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.main-list th { background-color: #f8f9fa; color: #800020; font-size: 9px; font-weight: bold; padding: 10px 5px; text-align: center; border: 1px solid #dee2e6; text-transform: uppercase; }
    table.main-list td { font-size: 9px; padding: 8px 5px; text-align: center; border: 1px solid #f1f1f1; }
    table.main-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="club-brand">CADA</div>
    <div class="report-title">Listado General de Atletas</div>
    <div class="report-meta">Generado el {$fechaGeneracion} · Total: {$esc(count($atletas))}</div>
</div>
<table class="main-list">
    <thead><tr><th>#</th><th style="text-align:left;">Atleta</th><th>Cédula</th><th>Categoría</th><th>Teléfono</th><th>Estatus</th></tr></thead>
    <tbody>{$rows}</tbody>
</table>
HTML;
    }
}
