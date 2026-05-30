<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;

final class ReporteUsuarioService
{
    public function listaUsuarios(): array
    {
        $usuarios = (new Usuario())->allWithRol();

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $rows = '';
        foreach ($usuarios as $index => $u) {
            $estatusTexto = $u['estatus'] ?? 'Activo';
            $rows .= sprintf(
                '<tr>' .
                '<td width="5%%" style="color:#999;text-align:center;">%d</td>' .
                '<td width="30%%" style="text-align:left;font-weight:bold;color:#1a1a1a;">%s</td>' .
                '<td width="15%%" style="text-align:center;">%s</td>' .
                '<td width="20%%" style="text-align:center;">%s</td>' .
                '<td width="15%%" style="text-align:center;">%s</td>' .
                '<td width="15%%" style="text-align:center;"><span style="font-weight:bold;color:#800020;">%s</span></td>' .
                '</tr>',
                $index + 1,
                $esc($u['apellido'] . ', ' . $u['nombre']),
                $esc($u['cedula'] ?? '—'),
                $esc($u['nombre_rol'] ?? '—'),
                $esc(!empty($u['telefono']) ? $u['telefono'] : '—'),
                $esc($estatusTexto)
            );
        }

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; }
    .document-header { text-align: center; padding-bottom: 15px; margin-bottom: 25px; border-bottom: 2px solid #800020; }
    .report-title { background-color: #800020; color: #ffffff; padding: 6px 20px; font-size: 14px; display: inline-block; font-weight: bold; text-transform: uppercase; }
    .report-meta { font-size: 11px; color: #555; margin-top: 10px; }
    table.main-list { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.main-list th { background-color: #f8f9fa; color: #800020; font-size: 9px; font-weight: bold; padding: 10px 5px; text-align: center; border: 1px solid #dee2e6; text-transform: uppercase; }
    table.main-list td { font-size: 9px; padding: 8px 5px; vertical-align: middle; border: 1px solid #dee2e6; }
    table.main-list tr:nth-child(even) { background-color: #fafafa; }
</style>
<div class="document-header">
    <div class="report-title">Listado General de Usuarios</div>
    <div class="report-meta">Total de usuarios registrados: <strong>{$esc(count($usuarios))}</strong></div>
</div>
<table class="main-list">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="30%" style="text-align:left;">Usuario</th>
            <th width="15%">Cédula</th>
            <th width="20%">Rol / Cargo</th>
            <th width="15%">Teléfono</th>
            <th width="15%">Estatus</th>
        </tr>
    </thead>
    <tbody>{$rows}</tbody>
</table>
HTML;

        $filename = 'listado_usuarios_' . date('Ymd');
        
        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render('Listado General de Usuarios', $html, strtolower($filename));
        }
        
        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }

    public function fichaUsuario(int $id): ?array
    {
        $u = (new Usuario())->findCompleto($id);
        if (!$u) return null;

        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $nombreCompleto = $esc($u['nombre'] . ' ' . $u['apellido']);
        
        $direccionParts = array_filter([$u['localidad'] ?? null, $u['parroquia_nombre'] ?? null, $u['municipio_nombre'] ?? null, $u['estado_nombre'] ?? null]);
        $direccion = $direccionParts ? $esc(implode(', ', $direccionParts)) : '—';
        $tipoVivienda = !empty($u['tipo_vivienda']) ? $esc(ucfirst($u['tipo_vivienda'])) : '—';
        $direccionExacta = !empty($u['ubicacion_vivienda']) ? $esc($u['ubicacion_vivienda']) : '—';

        $fotoHtml = '';
        if (!empty($u['foto'])) {
            $fotoPath = realpath(BASE_PATH . '/public' . $u['foto']);
            if ($fotoPath && file_exists($fotoPath)) {
                $b64 = base64_encode(file_get_contents($fotoPath));
                $fotoHtml = '<img src="data:image/jpeg;base64,' . $b64 . '" width="90" height="90" style="border-radius: 6px; border: 2px solid #800020;" />';
            }
        }

        $html = <<<HTML
<style>
    body { font-family: helvetica, sans-serif; color: #333; line-height: 1.8; }
    .section { margin-bottom: 25px; }
    .section-header { 
        background-color: #f8f9fa; 
        border-bottom: 2px solid #800020; 
        padding: 8px 10px; 
        margin-bottom: 15px; 
        font-size: 14px; 
        font-weight: bold; 
        color: #800020; 
        text-transform: uppercase; 
        letter-spacing: 1px;
    }
    table.info-grid { width: 100%; margin-bottom: 12px; }
    table.info-grid td { padding: 6px; vertical-align: top; border-bottom: 1px solid #f0f0f0; line-height: 1.5; }
    .info-label { font-weight: bold; color: #800020; width: 35%; font-size: 11px; }
    .info-value { color: #333; width: 65%; font-size: 11px; }
    .status-badge { color: #800020; border: 1px solid #800020; padding: 3px 12px; font-size: 12px; font-weight: bold; }
</style>
<div class="section">
    <table width="100%">
        <tr>
            <td width="75%">
                <h2 style="margin:0; color:#1a1a1a; font-size: 20px;">{$nombreCompleto}</h2>
                <p style="margin:5px 0 12px 0;"><span class="status-badge">Estatus: {$esc($u['estatus'] ?? 'Activo')}</span></p>
            </td>
            <td width="25%" style="text-align: right; vertical-align: top;">
                {$fotoHtml}
            </td>
        </tr>
    </table>
    <div style="line-height: 18px; height: 18px;">&nbsp;</div>
    <div class="section-header">Información Personal</div>
    <table class="info-grid">
        <tr>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Cédula:</td><td class="info-value">{$esc($u['cedula'] ?? '—')}</td></tr>
                    <tr><td class="info-label">Nacimiento:</td><td class="info-value">{$esc($u['fecha_nac'] ? date('d/m/Y', strtotime($u['fecha_nac'])) : '—')}</td></tr>
                    <tr><td class="info-label">Teléfono:</td><td class="info-value">{$esc($u['telefono'] ?? '—')}</td></tr>
                </table>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <table width="100%">
                    <tr><td class="info-label">Correo:</td><td class="info-value">{$esc($u['correo'] ?? '—')}</td></tr>
                    <tr><td class="info-label">Rol / Cargo:</td><td class="info-value">{$esc($u['nombre_rol'] ?? '—')}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>

<div class="section">
    <div class="section-header">Información de Residencia</div>
    <table class="info-grid">
        <tr>
            <td width="100%">
                <table width="100%">
                    <tr><td class="info-label" style="width:20%">Sector/Localidad:</td><td class="info-value" style="width:80%">{$direccion}</td></tr>
                    <tr><td class="info-label" style="width:20%">Tipo Vivienda:</td><td class="info-value" style="width:80%">{$tipoVivienda}</td></tr>
                    <tr><td class="info-label" style="width:20%">Dirección Exacta:</td><td class="info-value" style="width:80%">{$direccionExacta}</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>
HTML;

        $filename = 'ficha_usuario_' . preg_replace('/[^a-z0-9]+/i', '_', $u['nombre'] . '_' . $u['apellido']) . '_' . date('Ymd');
        
        if (class_exists(PdfGenerator::class)) {
            return (new PdfGenerator())->render('Ficha de Usuario - ' . $u['nombre'] . ' ' . $u['apellido'], $html, strtolower($filename));
        }
        
        return ['mime' => 'text/html', 'filename' => $filename . '.html', 'content' => $html];
    }
}
