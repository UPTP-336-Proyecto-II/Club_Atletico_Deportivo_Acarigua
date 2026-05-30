<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo para la tabla `resultados_pruebas` de cada_db.
 */
final class ResultadoPrueba extends Model
{
    protected string $table = 'resultados_pruebas';
    protected string $primaryKey = 'test_id';

    public function historial(int $atletaId): array
    {
        $rows = $this->query(
            'SELECT rp.*, act.fecha AS fecha_evento, act.tipo_actividad AS tipo_evento, act.usuario_id,
                    u.nombre AS nombre_entrenador, u.apellido AS apellido_entrenador, a.fecha_nac
             FROM resultados_pruebas rp
             JOIN actividades act ON act.actividad_id = rp.actividad_id
             LEFT JOIN usuarios u ON u.usuario_id = act.usuario_id
             JOIN atletas a ON a.atleta_id = rp.atleta_id
             WHERE rp.atleta_id = :a
             ORDER BY act.fecha DESC',
            [':a' => $atletaId]
        );

        foreach ($rows as &$row) {
            // Preservar valores crudos originales
            $row['test_de_fuerza_raw'] = $row['test_de_fuerza'] !== null ? (float)$row['test_de_fuerza'] : null;
            $row['test_resistencia_raw'] = $row['test_resistencia'] !== null ? (float)$row['test_resistencia'] : null;
            $row['test_velocidad_raw'] = $row['test_velocidad'] !== null ? (float)$row['test_velocidad'] : null;
            $row['test_coordinacion_raw'] = $row['test_coordinacion'] !== null ? (float)$row['test_coordinacion'] : null;
            $row['test_de_reaccion_raw'] = $row['test_de_reaccion'] !== null ? (float)$row['test_de_reaccion'] : null;

            if (!empty($row['fecha_nac'])) {
                $puntajes = $this->calcularPuntajes($row, (string)$row['fecha_nac']);
                $row['test_de_fuerza'] = $puntajes['test_de_fuerza'];
                $row['test_resistencia'] = $puntajes['test_resistencia'];
                $row['test_velocidad'] = $puntajes['test_velocidad'];
                $row['test_coordinacion'] = $puntajes['test_coordinacion'];
                $row['test_de_reaccion'] = $puntajes['test_de_reaccion'];
            }
        }
        unset($row);

        return $rows;
    }

    public function calcularEdad(string $fechaNac): int
    {
        try {
            $birthDate = new \DateTime($fechaNac);
            $today = new \DateTime('today');
            return $birthDate->diff($today)->y;
        } catch (\Throwable $e) {
            return 20;
        }
    }

    public function obtenerFactorExigencia(int $edad): float
    {
        return match (true) {
            $edad <= 6  => 0.40,
            $edad <= 9  => 0.55,
            $edad <= 12 => 0.70,
            $edad <= 15 => 0.85,
            $edad <= 18 => 0.95,
            $edad <= 40 => 1.00,
            $edad <= 49 => 0.85,
            $edad <= 59 => 0.70,
            $edad <= 69 => 0.55,
            default     => 0.40,
        };
    }

    private function calcularDirecta(float $resultado, float $minBase, float $maxBase, float $factor): float
    {
        $minAdaptado = $minBase * $factor;
        $maxAdaptado = $maxBase * $factor;
        if (abs($maxAdaptado - $minAdaptado) < 0.0001) {
            return 0.0;
        }
        $score = (($resultado - $minAdaptado) / ($maxAdaptado - $minAdaptado)) * 100;
        return max(0.0, min(100.0, round($score, 1)));
    }

    private function calcularInversa(float $resultado, float $minBase, float $maxBase, float $factor): float
    {
        if ($factor == 0.0) {
            return 0.0;
        }
        $minAdaptado = $minBase / $factor;
        $maxAdaptado = $maxBase / $factor;
        if (abs($minAdaptado - $maxAdaptado) < 0.0001) {
            return 0.0;
        }
        $score = (($minAdaptado - $resultado) / ($minAdaptado - $maxAdaptado)) * 100;
        return max(0.0, min(100.0, round($score, 1)));
    }

    private function calcularPuntajes(array $row, string $fechaNac): array
    {
        $edad = $this->calcularEdad($fechaNac);
        $factor = $this->obtenerFactorExigencia($edad);

        return [
            'test_de_fuerza'    => $row['test_de_fuerza'] !== null ? $this->calcularDirecta((float)$row['test_de_fuerza'], 20.0, 45.0, $factor) : null,
            'test_resistencia'  => $row['test_resistencia'] !== null ? $this->calcularDirecta((float)$row['test_resistencia'], 600.0, 2200.0, $factor) : null,
            'test_velocidad'    => $row['test_velocidad'] !== null ? $this->calcularInversa((float)$row['test_velocidad'], 5.20, 4.10, $factor) : null,
            'test_coordinacion' => $row['test_coordinacion'] !== null ? $this->calcularInversa((float)$row['test_coordinacion'], 22.50, 16.50, $factor) : null,
            'test_de_reaccion'  => $row['test_de_reaccion'] !== null ? $this->calcularInversa((float)$row['test_de_reaccion'], 450.0, 220.0, $factor) : null,
        ];
    }
}
