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
        return $this->query(
            'SELECT rp.*, act.fecha AS fecha_evento, act.tipo_actividad AS tipo_evento, act.usuario_id,
                    u.nombre AS nombre_entrenador, u.apellido AS apellido_entrenador
             FROM resultados_pruebas rp
             JOIN actividades act ON act.actividad_id = rp.actividad_id
             LEFT JOIN usuarios u ON u.usuario_id = act.usuario_id
             WHERE rp.atleta_id = :a
             ORDER BY act.fecha DESC',
            [':a' => $atletaId]
        );
    }
}
