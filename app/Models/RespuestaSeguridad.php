<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

final class RespuestaSeguridad extends Model
{
    protected string $table = 'respuestas_seguridad';
    protected string $primaryKey = 'respuesta_id';

    public function saveRespuesta(int $userId, int $preguntaId, string $respuesta): void
    {
        $hash = password_hash(strtolower(trim($respuesta)), PASSWORD_BCRYPT);
        $this->query(
            'INSERT INTO respuestas_seguridad (usuario_id, pregunta_id, respuesta) VALUES (?, ?, ?)',
            [$userId, $preguntaId, $hash]
        );
    }

    public function getByUser(int $userId): array
    {
        return $this->query(
            'SELECT rs.respuesta_id, rs.pregunta_id, ps.preguntas
             FROM respuestas_seguridad rs
             JOIN preguntas_seguridad ps ON ps.pregunta_id = rs.pregunta_id
             WHERE rs.usuario_id = ?
             ORDER BY rs.respuesta_id',
            [$userId]
        );
    }

    /**
     * Obtiene las preguntas con sus respuestas hasheadas (para verificación en recuperación).
     */
    public function getByUserWithAnswers(int $userId): array
    {
        return $this->query(
            'SELECT rs.respuesta_id, rs.pregunta_id, ps.preguntas, rs.respuesta
             FROM respuestas_seguridad rs
             JOIN preguntas_seguridad ps ON ps.pregunta_id = rs.pregunta_id
             WHERE rs.usuario_id = ?
             ORDER BY rs.respuesta_id',
            [$userId]
        );
    }

    public function deleteByUser(int $userId): void
    {
        $this->query('DELETE FROM respuestas_seguridad WHERE usuario_id = ?', [$userId]);
    }
}
