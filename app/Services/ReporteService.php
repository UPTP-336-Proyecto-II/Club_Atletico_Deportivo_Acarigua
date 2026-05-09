<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Atleta;
use App\Models\MedidaAntropometrica;
use App\Models\ResultadoPrueba;
use App\Models\Asistencia;

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
        
        // Asumiendo que PdfGenerator existe y funciona. Si no, retornamos HTML simple por ahora.
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

    private function construirHtml(array $a, array $antropo, array $pruebas, array $asistencia): string
    {
        $esc = fn($v) => htmlspecialchars((string) ($v ?? '—'), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $nombreCompleto = $esc($a['nombre'] . ' ' . $a['apellido']);
        $fechaNac = $a['fecha_nac'] ?? null;
        $edad = $fechaNac
            ? (new \DateTime($fechaNac))->diff(new \DateTime('today'))->y
            : null;

        // Antropometría — tabla
        $antropoRows = '';
        foreach ($antropo as $m) {
            $antropoRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($m['fecha_medicion']),
                $esc($m['peso']),
                $esc($m['altura']),
                $esc($m['envergadura']),
                $esc($m['porcentaje_grasa']),
                $esc($m['porcentaje_musculatura'])
            );
        }
        $antropoTable = $antropoRows
            ? "<table><thead><tr><th>Fecha</th><th>Peso (kg)</th><th>Altura (cm)</th><th>Envergadura</th><th>% Grasa</th><th>% Musc.</th></tr></thead><tbody>$antropoRows</tbody></table>"
            : '<p>Sin mediciones antropométricas registradas.</p>';

        // Pruebas físicas
        $pruebasRows = '';
        foreach ($pruebas as $p) {
            $pruebasRows .= sprintf(
                '<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                $esc($p['fecha_evento']),
                $esc($p['test_de_fuerza']),
                $esc($p['test_resistencia']),
                $esc($p['test_velocidad']),
                $esc($p['test_coordinacion']),
                $esc($p['test_de_reaccion'])
            );
        }
        $pruebasTable = $pruebasRows
            ? "<table><thead><tr><th>Fecha</th><th>Fuerza</th><th>Resistencia</th><th>Velocidad</th><th>Coordinación</th><th>Reacción</th></tr></thead><tbody>$pruebasRows</tbody></table>"
            : '<p>Sin pruebas físicas registradas.</p>';

        // Asistencia
        $asiMap = [1 => 0, 0 => 0, 2 => 0]; // 1: Presente, 0: Ausente, 2: Justificado?
        foreach ($asistencia as $row) {
            $asiMap[(int)$row['estatus']] = (int) $row['total'];
        }
        $presentes = $asiMap[1];
        $ausentes = $asiMap[0];
        $justificados = $asiMap[2];
        $totalAsi = array_sum($asiMap) ?: 1;
        $pctPresente = round(($presentes / $totalAsi) * 100, 1);

        $direccionParts = array_filter([
            $a['localidad'] ?? null,
            $a['parroquia_nombre'] ?? null, $a['municipio_nombre'] ?? null, $a['estado_nombre'] ?? null
        ]);
        $direccion = $direccionParts ? $esc(implode(', ', $direccionParts)) : '—';
        
        $estatusTexto = ((int)$a['estatus'] === 1) ? 'Activo' : (((int)$a['estatus'] === 2) ? 'Lesionado' : 'Suspendido');

        return <<<HTML
<div class="header">
    <h1 class="header-title">Ficha Técnica Individual</h1>
    <p style="margin:4px 0 0; color: #6B7280;">
        Club Atlético Deportivo Acarigua · Generado el {$esc(date('Y-m-d H:i'))}
    </p>
</div>

<h2>{$nombreCompleto}</h2>
<span class="badge">{$esc($estatusTexto)}</span>

<div class="grid-2" style="margin-top:16px;">
    <div>
        <p><strong>Cédula:</strong> {$esc($a['cedula'])}</p>
        <p><strong>Fecha de nacimiento:</strong> {$esc($fechaNac)} ({$esc($edad)} años)</p>
        <p><strong>Teléfono:</strong> {$esc($a['telefono'])}</p>
        <p><strong>Categoría:</strong> {$esc($a['nombre_categoria'])}</p>
        <p><strong>Posición:</strong> {$esc($a['nombre_posicion'])}</p>
        <p><strong>Pierna dominante:</strong> {$esc($a['pierna_dominante'])}</p>
    </div>
    <div>
        <p><strong>Dirección:</strong> {$direccion}</p>
        <p><strong>Representante:</strong> {$esc($a['representante_nombre'])}</p>
        <p><strong>Cédula Representante:</strong> {$esc($a['representante_cedula'])}</p>
        <p><strong>Relación:</strong> {$esc($a['representante_relacion'])}</p>
        <p><strong>Teléfono representante:</strong> {$esc($a['representante_telefono'])}</p>
        <p><strong>Grupo sanguíneo:</strong> {$esc($a['grupo_sanguineo'])}</p>
    </div>
</div>

<h3>📏 Antropometría</h3>
{$antropoTable}

<h3>⚡ Pruebas físicas</h3>
{$pruebasTable}

<h3>📋 Resumen de asistencia</h3>
<p><strong>Presentes:</strong> {$presentes} · <strong>Ausentes:</strong> {$ausentes} · <strong>Justificados:</strong> {$justificados}</p>
<p><strong>Porcentaje de asistencia:</strong> {$pctPresente}%</p>

<h3>🏥 Ficha médica</h3>
<p><strong>Alergias:</strong> {$esc($a['alergias'])}</p>
<p><strong>Antecedentes Familiares:</strong> {$esc($a['antecedentes_familiares'])}</p>
<p><strong>Antecedentes Quirúrgicos:</strong> {$esc($a['antecedentes_quirurgicos'])}</p>
<p><strong>Condición Crónica:</strong> {$esc($a['condicion_cronica'])}</p>
<p><strong>Medicación Actual:</strong> {$esc($a['medicacion_actual'])}</p>

HTML;
    }
}
