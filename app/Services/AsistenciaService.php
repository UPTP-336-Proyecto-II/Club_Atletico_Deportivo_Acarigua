<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Core\Logger;
use App\Models\Actividad;
use Throwable;

final class AsistenciaService
{
    /**
     * Registra un evento (entrenamiento/pruebas/etc.) y bulk insert de asistencias.
     *
     * @param array<int, array{atleta_id:int, estatus:int|string, observaciones?:string}> $detalles
     */
    public function registrar(
        int $entrenadorId,
        string $tipoEvento,
        string $fechaEvento,
        array $detalles,
        int $categoriaId,
        ?string $horaInicio = null,
        ?string $horaFin = null,
        ?string $ubicacion = null,
        ?int $clima = null
    ): int {
        if (empty($detalles)) {
            throw new \RuntimeException('Debes marcar la asistencia de al menos un atleta.');
        }

        // Mapeo de tipos de evento a tinyint (según cada_db_clean.sql)
        $tipoMap = [
            'Partido'         => 0,
            'Entrenamiento'   => 1,
            'Pruebas Físicas' => 2,
            'Evento Especial' => 3
        ];
        $tipoId = $tipoMap[$tipoEvento] ?? 1;

        // Validar duplicado para la misma categoría, fecha y tipo
        $db = Database::connection();
        $stmt = $db->prepare("
            SELECT a.actividad_id 
            FROM actividades a
            JOIN asistencias ast ON a.actividad_id = ast.actividad_id
            JOIN atletas atl ON ast.atleta_id = atl.atleta_id
            WHERE a.fecha = ? AND a.tipo_actividad = ? AND atl.categoria_id = ?
            LIMIT 1
        ");
        $stmt->execute([$fechaEvento, $tipoId, $categoriaId]);
        if ($stmt->fetch()) {
            throw new \RuntimeException("Ya existe un registro de $tipoEvento para esta categoría en la fecha seleccionada.");
        }

        Database::beginTransaction();
        try {
            $eventoId = (new Actividad())->insert([
                'usuario_id'     => $entrenadorId,
                'tipo_actividad' => $tipoId,
                'fecha'          => $fechaEvento,
                'hora_inicio'    => $horaInicio,
                'hora_fin'       => $horaFin,
                'ubicacion'      => $ubicacion ?: 'Cancha UPTP',
                'clima'          => $clima,
            ]);

            $stmt = Database::connection()->prepare(
                'INSERT INTO asistencias (actividad_id, atleta_id, estatus, observaciones)
                 VALUES (:e, :a, :s, :o)'
            );
            foreach ($detalles as $d) {
                // El estatus ya viene mapeado correctamente desde el controlador (0, 1, 2)
                $est = (int) $d['estatus'];
                
                $stmt->execute([
                    ':e' => $eventoId,
                    ':a' => (int) $d['atleta_id'],
                    ':s' => $est,
                    ':o' => $d['observaciones'] ?? null,
                ]);
            }

            Database::commit();
            Logger::audit('asistencia.pase', ['actividad_id' => $eventoId, 'total' => count($detalles)]);
            return $eventoId;
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function actualizar(
        int $eventoId,
        int $entrenadorId,
        string $tipoEvento,
        string $fechaEvento,
        array $detalles,
        ?string $horaInicio = null,
        ?string $horaFin = null,
        ?string $ubicacion = null,
        ?int $clima = null
    ): void {
        $tipoMap = [
            'Partido'         => 0,
            'Entrenamiento'   => 1,
            'Pruebas Físicas' => 2,
            'Evento Especial' => 3
        ];
        $tipoId = $tipoMap[$tipoEvento] ?? 1;

        Database::beginTransaction();
        try {
            // Actualizar actividad
            (new Actividad())->update($eventoId, [
                'usuario_id'     => $entrenadorId,
                'tipo_actividad' => $tipoId,
                'fecha'          => $fechaEvento,
                'hora_inicio'    => $horaInicio,
                'hora_fin'       => $horaFin,
                'ubicacion'      => $ubicacion ?: 'Cancha UPTP',
                'clima'          => $clima,
            ]);

            // Eliminar asistencias anteriores
            $db = Database::connection();
            $stmt = $db->prepare("DELETE FROM asistencias WHERE actividad_id = ?");
            $stmt->execute([$eventoId]);

            // Insertar nuevas
            $stmt = $db->prepare(
                'INSERT INTO asistencias (actividad_id, atleta_id, estatus, observaciones)
                 VALUES (:e, :a, :s, :o)'
            );
            foreach ($detalles as $d) {
                $est = (int) $d['estatus'];
                $stmt->execute([
                    ':e' => $eventoId,
                    ':a' => (int) $d['atleta_id'],
                    ':s' => $est,
                    ':o' => $d['observaciones'] ?? null,
                ]);
            }

            Database::commit();
            Logger::audit('asistencia.update', ['actividad_id' => $eventoId, 'total' => count($detalles)]);
        } catch (Throwable $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
